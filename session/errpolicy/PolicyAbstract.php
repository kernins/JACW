<?php
namespace lib\dp\Curl\session\errpolicy;
use lib\dp\Curl\session, lib\dp\Curl\exception;


abstract class PolicyAbstract
   {
      protected const THROWABLE_NORESP    =  exception\transfer\BadResponseException::class;
      protected const THROWABLE_CURLDFLT  =  exception\transfer\CurlErrorException::class;
   
      protected const CURL_ERRORS_MAP = [
         curlcode\NetworkError::class     => exception\transfer\NetworkErrorException::class,
         curlcode\ProtocolError::class    => exception\transfer\ProtocolErrorException::class,
         curlcode\SSLError::class         => exception\transfer\SSLErrorException::class,
         curlcode\ServerError::class      => exception\transfer\ServerErrorException::class,
         curlcode\BadResponseError::class => exception\transfer\BadResponseException::class
      ];
      
      protected const CURL_ERRORS_RETRYABLE = [
         curlcode\NetworkError::class,
         curlcode\ProtocolError::class,
         curlcode\BadResponseError::class
      ];
      
      
      protected int  $maxRetriesAllowed;
      protected bool $responseRequired;
      
      
      
      public function __construct(int $maxRetries=0, bool $respRequired=true)
         {
            if($maxRetries < 0) throw new exception\InvalidArgumentException('Retries limit may not be negative');
         
            $this->maxRetriesAllowed = $maxRetries;
            $this->responseRequired = $respRequired;
         }
      
      
      final public function evaluate(session\InfoProvider $sessIP, bool $hasSrvResponse): ?Error
         {
            /* Checking response code first as it is proto-specific and can produce more specific error
             * Empty (0) code would most likely mean no response at all, excluding for now
             */
            if(!empty($respCode=$sessIP->getInfoRespCode()))
               $err = $this->evaluateResponseCode($respCode);
         
            // Evaluating libcurl error code next
            if(empty($err) && $sessIP->hasPendingError())
               {
                  $curlCode = $sessIP->getLastErrorCode();
                  if(!empty($ec=$this->getKnownCurlErrorCaseForCode($curlCode)))
                     $err = new Error($sessIP->getLastErrorMessage(), $curlCode, static::CURL_ERRORS_MAP[$ec::class], ...$this->getRetriesLimitAndDelayForCurlError($ec));
                  elseif(!empty(static::THROWABLE_CURLDFLT))
                     $err = new Error($sessIP->getLastErrorMessage(), $curlCode, static::THROWABLE_CURLDFLT, 0); //non-retryable
               }
            
            /* Last resort check for cases when server Response is set required.
             * This denotes a weird situation when no response was received from server
             * but still transfer is considered successfull by libcurl
             */
            if(empty($err) && !$hasSrvResponse && $this->responseRequired)
               $err = new Error('No response from server', $respCode, static::THROWABLE_NORESP, ...$this->getRetriesLimitAndDelayForNoRespError());
            
            return $err;
         }
      
      /*
       * The concept of response-code is applicable to multiple protos (e.g. http, ftp, smtp)
       * Exact values and their meanings are protocol specific however
       * 
       * @param int $respCode
       * @return Error|null
       */
      abstract protected function evaluateResponseCode(int $respCode): ?Error;
      
      
      protected function getKnownCurlErrorCaseForCode(int $code): ?ICurlErrorCase
         {
            $case = null;
            foreach(array_keys(static::CURL_ERRORS_MAP) as $enum)
               {
                  /* @var $enum ICurlErrorCase */
                  if(!empty($case=$enum::tryFrom($code))) break;
               }
            return $case;
         }
      
      /**
       * @param ICurlErrorCase $err
       * @return array  [int limit, int delaySeconds]
       */
      protected function getRetriesLimitAndDelayForCurlError(ICurlErrorCase $err): array
         {
            return [in_array($err::class, static::CURL_ERRORS_RETRYABLE)? $this->maxRetriesAllowed:0, 0];
         }
      
      /**
       * @return array  [int limit, int delaySeconds]
       */
      protected function getRetriesLimitAndDelayForNoRespError(): array
         {
            return [$this->maxRetriesAllowed, 0];
         }
   }
