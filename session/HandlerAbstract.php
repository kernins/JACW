<?php
namespace lib\dp\Curl\session;
use lib\dp\Curl\exception;


abstract class HandlerAbstract
   {
      protected \CurlHandle   $hndl;
      
      protected Config        $config;
      protected IRequest      $request;
      /**
       * Meant to be initialized just-in-time,
       * indicates presence of actual server response
       * @var IResponse
       */
      protected ?IResponse    $response = null;
   
      
      
      public function __construct(?IRequest $request=null, ?Config $config = null)
         {
            if($request !== null) $this->setRequest($request);
            if($config !== null) $this->setConfig($config);
         }
         
      public function __destruct()
         {
            if(!empty($this->hndl)) curl_close($this->hndl);
         }
      
      
      public function setRequest(IRequest $req): self
         {
            $this->request = $req;
            return $this;
         }
         
      public function setConfig(Config $cfg): self
         {
            if(empty($this->config)) $this->config = $cfg; //TODO: clone?
            else $this->config->merge($cfg);
            return $this;
         }
      
      
      public function getConfig(): Config
         {
            return $this->config;
         }
      
         
      public function getHandle(): \CurlHandle
         {
            if(empty($this->hndl)) throw new exception\BadMethodCallException(
               __METHOD__.'() must not be called before curl session is initialized'
            );
            return $this->hndl;
         }
      
      public function isInitialized(): bool
         {
            return !empty($this->hndl);
         }
      
      
      final public function init(): self
         {
            $this->hndl = curl_init();
            curl_setopt_array($this->hndl, $this->config->toArray());
            curl_setopt_array($this->hndl, $this->request->toArray());
            
            $this->initHandlers();
            return $this;
         }
         
      abstract protected function initHandlers(): void;
      abstract protected function initResponse(): void;
      
      
      public function exec(): static
         {
            curl_exec($this->hndl);
            //var_dump(curl_getinfo($this->hndl,  CURLINFO_HEADER_OUT));
            //var_dump(curl_getinfo($this->hndl, CURLINFO_PRIVATE));
            //$this->getResponse()->setData($resp);
            return $this;
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
