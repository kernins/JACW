<?php
namespace lib\dp\Curl\async;
use lib\dp\Curl\session, lib\dp\Curl\exception;


abstract class DispatcherAbstract
   {
      protected const SELECT_TIMO      = 0.1;
      //Watchdog threasholds
      protected const WDT_NO_ACTIVITY  = 60.0;
      protected const WDT_NO_FDS       = 60.0;
      
   
      private \CurlMultiHandle   $_hndl;
      private int                $_numRunningTransfers = 0;
      
      
      protected int              $maxActive;
      
      protected IQueue           $transPending;
      protected array            $transActive = [];
      
      
      
      public function __construct(int $maxActive=5)
         {
            if($maxActive <= 0) throw new exception\OutOfRangeException('MaxActive transfers limit must be greater than zero');
            $this->maxActive = $maxActive;
         
            if(($this->_hndl=curl_multi_init()) === false)
               throw new exception\RuntimeException('Failed to initialize CurlMulti handle');
         }
      
      
      /**
       * Meant to be overridden to provide different queue impl when required
       * 
       * @return IQueue
       */
      public function getQueue(): IQueue
         {
            if(empty($this->transPending)) $this->transPending = new queue\Simple();
            return $this->transPending;
         }
      
      
      final public function exec(): static
         {
            if($this->getQueue()->isEmpty()) throw new exception\BadMethodCallException(__METHOD__.'() must not be called with empty queue');
         
            while($this->_tryStartNewTransfer());  //assuming non-empty queue must start at least one
            $this->_execActiveTransfers();         //initial setup

            $wdtNoActivity = $wdtNoFDs = 0.0;      //watchdogs
            while(count($this->transActive))
               {
                  /* Notes:
                   * There was quite a few BC-breaking changes in both libcurl itself and its PHP bindings since old-good PHP5 times
                   * One of those changes was that curl_multi_select() started to return -1 if cURL had no open file descriptors
                   * Since PHP 7.1.23/7.2.11 and libcurl 7.28 it will again return 0 in that case and -1 only on error
                   * Hope they won't break it again... Internally it now uses curl_multi_wait()
                   */
                  
                  /* Internal implementation (php 8.0):
                   * error = curl_multi_wait(mh->multi, NULL, 0, (unsigned long) (timeout * 1000.0), &numfds);
                   * if (CURLM_OK != error) {
                   *    SAVE_CURLM_ERROR(mh, error);
                   *    RETURN_LONG(-1);
                   * } 
                   * RETURN_LONG(numfds);
                   */
                  
                  /* CURL docs: @link https://curl.se/libcurl/c/curl_multi_wait.html
                   * curl_multi_wait() polls all file descriptors used by the curl easy handles contained in the given multi handle set.
                   * It will block until activity is detected on at least one of the handles or timeout_ms has passed.
                   * If no extra file descriptors are provided and libcurl has no file descriptor to offer to wait for, it will return immediately.
                   * 'numfds' being zero means either a timeout or no file descriptors to wait for.
                   * Try timeout on first occurrence, then assume no file descriptors and wait for 100 milliseconds
                   */
                  
                  $start=microtime(true);
                  switch(curl_multi_select($this->_hndl, static::SELECT_TIMO))
                     {
                        case -1: //internal libcurl error, e.g. failed select() syscall
                           throw new exception\RuntimeException('curl_multi_select() failed: internal libcurl error');
                        case  0: //no activity or no FDs to select() on
                           /* 
                            * in case there are no FDs (e.g. DNS-resolution stage, no conns were attempted yet),
                            * curl_multi_select() will return immediately
                            */
                           if(($selTime=microtime(true)-$start) < static::SELECT_TIMO)
                              {
                                 /* NB: as internally curl_multi_wait() is now being used instead of curl_multi_fdset(),
                                  * this could also be the case if some transfer's private timo expired be4 select_timo
                                  */
                                 $wdtNoFDs += static::SELECT_TIMO;
                                 if((static::WDT_NO_FDS>0) && ($wdtNoFDs>=static::WDT_NO_FDS)) throw new exception\WatchdogExpiredException(
                                    'No FDs to select() on for more than '.round($wdtNoFDs, 3).' seconds'
                                 );
                                 
                                 usleep((int)(static::SELECT_TIMO-$selTime)*1e6);
                              }
                           else
                              {
                                 $wdtNoActivity += static::SELECT_TIMO;
                                 if((static::WDT_NO_ACTIVITY>0) && ($wdtNoActivity>=static::WDT_NO_ACTIVITY)) throw new exception\WatchdogExpiredException(
                                    'No activity on individual transfers for more than '. round($wdtNoActivity, 3).' seconds'
                                 );
                              }
                           break;
                        default: //there is some activity on individual transfers
                           $wdtNoActivity = $wdtNoFDs = 0.0;
                     }
                  $this->_execActiveTransfers();
                  $this->_checkActiveTransfers();
               }
            return $this;
         }
      
      
      private function _tryStartNewTransfer(): bool
         {
            //if the below condition is met, this method MUST init & add a new transfer or throw on failure
            if((count($this->transActive)<$this->maxActive) && !$this->transPending->isEmpty())
               {
                  $trans = $this->transPending->dequeue();
                  $hndl = $trans->init()->getHandle();
                  
                  if(isset($this->transActive[(int)$hndl]))
                     throw new exception\LogicException('Curl session#'.(int)$hndl.' is already in stack');
                  if(($res=curl_multi_add_handle($this->_hndl, $hndl)) !== CURLM_OK)
                     throw new exception\RuntimeException('Failed to add curl session#'.(int)$hndl.' into stack', $res);

                  $this->transActive[(int)$hndl] = $trans;
                  return true;
               }
            else return false;
         }
         
      private function _execActiveTransfers(): void
         {
            /* Internal implementation (php 8.0)
             * still_running = zval_get_long(z_still_running);
             * error = curl_multi_perform(mh->multi, &still_running);
             * ZEND_TRY_ASSIGN_REF_LONG(z_still_running, still_running);
             * SAVE_CURLM_ERROR(mh, error);
             * RETURN_LONG((zend_long) error);
             */
         
            /* CURL docs: @link https://curl.se/libcurl/c/curl_multi_perform.html
             * curl_multi_perform() will transfer data on all current transfers in the multi stack that are ready to transfer anything,
             * It may be all, it may be none. When there's nothing more to do for now, it returns back to the calling application.
             * This function does not require that there actually is any data available for reading or that data can be written, 
             * it can be called just in case and will store the number of handles that still transfer data in the second argument's integer-pointer,
             * and by reading that you can figure out when all the transfers in the multi handles are done.
             * NB however if an added handle fails quickly, it may never be counted as a running_handle.
             * 
             * When this function returns error, the state of all transfers are uncertain and they cannot be continued.
             * curl_multi_perform() should not be called again on the same multi handle after an error has been returned,
             * unless first removing all the handles and adding new ones.
             */
         
            //NB: CURLM_CALL_MULTI_PERFORM is gone since long ago (libcurl 7.20.0+)
            if(($res=curl_multi_exec($this->_hndl, $this->_numRunningTransfers)) !== CURLM_OK)
               throw new exception\RuntimeException('curl_multi_exec() failed', $res);
         }
         
      private function _checkActiveTransfers(): void
         {
            /* CURL docs: @link https://curl.se/libcurl/c/curl_multi_info_read.html
             * Repeated invokes of the function get more messages until the message queue is empty
             * The data the returned resource points to will not survive calling curl_multi_remove_handle()
             * 
             * CURLMSG_DONE msg identifies a transfer that is done, result contains the return code for the easy handle that just completed
             * At this point, there are no other msg types defined
             */
            while(!empty($info=curl_multi_info_read($this->_hndl)))
               {
                  if($info['msg'] != CURLMSG_DONE) throw new exception\LogicException(
                     'Unknown CurlMulti info message type: '.$info['msg']
                  );
                  
                  $transID=(int)$info['handle'];
                  /* @var $trans session\HandlerAbstract */
                  $trans = $this->transActive[$transID];
                  unset($this->transActive[$transID]);
                  
                  if(($res=curl_multi_remove_handle($this->_hndl, $trans->getHandle())) != CURLM_OK)
                     throw new exception\RuntimeException('Failed to remove session #'.$transID.' handle', $res);
                  
                  //TODO: basic rety logic based on 'result'
                  
                  $this->onTransferCompleted($trans);
               }
         }
      
      /**
       * Handle completed transfer. Completed doesn't necessarily mean successful
       * May enqueue new transfers and may re-queue passed transfer for retry
       */
      abstract protected function onTransferCompleted(session\HandlerAbstract $trans): void;
   }
