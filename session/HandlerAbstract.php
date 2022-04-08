<?php
namespace lib\dp\Curl\session;
use lib\dp\Curl\exception;


abstract class HandlerAbstract
   {
      protected \CurlHandle   $hndl;
      protected InfoProvider  $infoProvider;
      
      protected Config        $config;
      protected IRequest      $request;
      
      /**
       * Meant to be initialized just-in-time (on getting actual server response),
       * and so indicates presence of such a response
       * @var IResponse
       */
      protected ?IResponse    $response = null;
      
      protected ?errpolicy\PolicyAbstract $errorPolicy = null;
      protected ?IExpectation             $expectation = null;
      
      
      
      public function __construct(?IRequest $request=null, ?Config $config = null)
         {
            if($request !== null) $this->setRequest($request);
            if($config !== null) $this->setConfig($config);
            
            //avoiding TypeError on fail
            if(($hndl=curl_init()) === false)
               throw new exception\RuntimeException('Failed to init curl session handle');
            
            $this->hndl = $hndl;
            $this->infoProvider = new InfoProvider($this->hndl);
         }
         
      public function __destruct()
         {
            /* NB: curl_close() is no-op starting from php 8.0
             * Session cleanup is now handled by CurlHandle destructor
             * TODO: remove on deprecation
             */
            curl_close($this->hndl);
         }
      
      
      public function setRequest(IRequest $req): static
         {
            $this->request = $req;
            return $this;
         }
         
      public function setConfig(Config $cfg): static
         {
            if(empty($this->config)) $this->config = $cfg; //TODO: clone?
            else $this->config->merge($cfg);
            return $this;
         }
      
      
      public function setErrorPolicy(errpolicy\PolicyAbstract $errPol): static
         {
            $this->errorPolicy = $errPol;
            return $this;
         }
      
      public function setExpectation(IExpectation $expectation): static
         {
            $this->expectation = $expectation;
            return $this;
         }
      
      
      /**
       * Is here primarily for CurlMulti / AsyncDispatcher
       * @return \CurlHandle
       */
      final public function getHandle(): \CurlHandle
         {
            return $this->hndl;
         }
      
      public function getConfig(): Config
         {
            if(empty($this->config)) throw new exception\BadMethodCallException(
               __METHOD__.'() must not be called before Config is set'
            );
            return $this->config;
         }
      
      public function getResponse(): ?IResponse
         {
            return $this->response;
         }
      
      final public function hasResponse(): bool
         {
            return !empty($this->response);
         }
      
      
      public function init(): static
         {
            if(empty($this->request) || empty($this->config)) throw new exception\BadMethodCallException(
               'Request and Config must both be set before calling '.__METHOD__.'()'
            );
            if($this->hasResponse()) throw new exception\BadMethodCallException(
               'Existing session must be reset before calling '.__METHOD__.'() again'
            );
            
            $this->setOptsGroup($this->config->toArray());
            
            if($this->expectation instanceof IRequestHintInjector)
               {
                  $req = clone $this->request;
                  $this->expectation->injectRequestHint($req);
               }
            $this->setOptsGroup(($req ?? $this->request)->toArray());
            
            //FIXME: use first class callable syntax, php 8.1+
            //Using class method to allow overrides
            $this->setOpt(\CURLOPT_WRITEFUNCTION, [$this, 'cbBodyWriter']);
            
            return $this;
         }
         
      final public function reset(): static
         {
            $this->response = null;
            curl_reset($this->hndl);
            return $this;
         }
      
      
      final public function execSimple(): static
         {
            if($this->hasResponse()) throw new exception\BadMethodCallException(
               'Existing session must be re-initialized before calling '.__METHOD__.'() again'
            );
            
            curl_exec($this->hndl);
            return $this;
         }
         
      final public function execSmart(): static
         {
            if(empty($this->errorPolicy)) throw new exception\BadMethodCallException(
               __METHOD__.'() requires an ErrorPolicy to be set'
            );
            
            $this->init();
            $retryAttempt = 0;
            do
               {
                  if($retryAttempt > 0) //this iteration is a retry
                     {
                        if(!empty($rds=$err->getRetryDelaySeconds())) sleep($rds);
                        $this->reset()->init();
                     }
                  
                  $this->execSimple();
                  $err = $this->checkError();
               }
            while(!empty($err) && $err->isRetryable(++$retryAttempt));
            $err?->throw();
            
            $this->validateExpectation();
            return $this;
         }
      
      public function checkError(): ?errpolicy\Error
         {
            return $this->errorPolicy?->evaluate($this->infoProvider, $this->hasResponse());
         }
         
      public function validateExpectation(): static
         {
            try {$this->expectation?->validate($this->infoProvider, $this->getResponse());}
            catch(\RuntimeException $ex)
               {
                  throw new exception\transfer\UnexpectedResponseException(
                     'Expectation failed: '.$ex->getMessage(),
                     $ex->getCode(),
                     $ex
                  );
               }
            return $this;
         }
      
      
      final protected function setOpt(int $opt, $val): void
         {
            if(!curl_setopt($this->hndl, $opt, $val)) throw new exception\RuntimeException(
               'Failed to set curl option['.$opt.']: '.$this->infoProvider->getLastErrorCode().': '.$this->infoProvider->getLastErrorMessage()
            );
         }
      
      final protected function setOptsGroup(array $opts): void
         {
            if(!curl_setopt_array($this->hndl, $opts)) throw new exception\RuntimeException(
               'Failed to set options group: '.$this->infoProvider->getLastErrorCode().': '.$this->infoProvider->getLastErrorMessage()
            );
         }
      
      
      /**
       * CURLOPT_WRITEFUNCTION callback
       * 
       * @param \CurlHandle   $hndl
       * @param string        $chunk
       * @return int
       */
      protected function cbBodyWriter(\CurlHandle $hndl, string $chunk): int
         {
            if(!$this->hasResponse()) $this->initResponse(); //JIT
            
            $this->getResponse()->appendData($chunk);
            return strlen($chunk);
         }
      
      abstract protected function initResponse(): void;
   }
