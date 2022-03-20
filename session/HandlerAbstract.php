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
            if(!empty($this->hndl)) curl_close($this->hndl);
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
            $this->setOptsGroup($this->request->toArray());
            
            //FIXME: use first class callable syntax, php 8.1+
            //Using class method to allow overrides
            $this->setOpt(CURLOPT_WRITEFUNCTION, [$this, 'cbBodyWriter']);
            
            return $this;
         }
      
      final public function reset(): static
         {
            $this->response = null;
            curl_reset($this->hndl);
            return $this;
         }
      
      
      final public function exec(bool $skipErrorChecking=false): static
         {
            if($this->hasResponse()) throw new exception\BadMethodCallException(
               'Existing session must be re-initialized before calling '.__METHOD__.'() again'
            );
         
            curl_exec($this->hndl);
            if(!$skipErrorChecking && !empty($err=$this->checkError()))
               {
                  throw $err->getThrowable();
               }
            return $this;
         }
      
      public function checkError(): ?errpolicy\Error
         {
            return $this->errorPolicy?->evaluate($this->infoProvider);
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
