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
       * and so indicates presence of such response
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
      
      
      public function getConfig(): Config
         {
            return $this->config;
         }
      
      /**
       * This is primarily for Curl-Multi
       * @return \CurlHandle
       */
      public function getHandle(): \CurlHandle
         {
            return $this->hndl;
         }
      
         
      final public function init(): static
         {
            if(empty($this->request) || empty($this->config)) throw new exception\BadMethodCallException(
               'Request and Config must both be set before calling '.__METHOD__.'()'
            );
            if(!empty($this->response)) throw new exception\BadMethodCallException(
               'Existing session must be reset before calling '.__METHOD__.'() again'
            );
         
            curl_setopt_array($this->hndl, $this->config->toArray());
            curl_setopt_array($this->hndl, $this->request->toArray());
            
            $this->initHandlers();
            return $this;
         }
      abstract protected function initHandlers(): void;
      
      final public function reset(): static
         {
            $this->response = null;
            curl_reset($this->hndl);
            return $this;
         }
      
      
      final public function exec(bool $skipErrorChecking=false): static
         {
            if(!empty($this->response)) throw new exception\BadMethodCallException(
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
      
      
      final public function hasResponse(): bool
         {
            return !empty($this->response);
         }
         
      final public function getResponse(): ?IResponse
         {
            return $this->response;
         }
   }
