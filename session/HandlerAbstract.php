<?php
namespace lib\dp\Curl\session;


abstract class HandlerAbstract
   {
      protected $hndl = null;
      
      protected Config     $config;
      protected IRequest   $request;
   
      
      
      public function __construct(?IRequest $request=null, ?Config $config = null)
         {
            if($request !== null) $this->setRequest($request);
            if($config !== null) $this->setConfig($config);
         }
         
      public function __destruct()
         {
            if($this->hndl !== null) curl_close($this->hndl);
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
      
      
      final public function init(): self
         {
            $this->hndl = curl_init();
            curl_setopt_array($this->hndl, $this->config->toArray());
            curl_setopt_array($this->hndl, $this->request->toArray());
            $this->initHandlers();
            
            return $this;
         }
         
      abstract protected function initHandlers(): void;
         
         
      public function exec(): self
         {
            $resp = curl_exec($this->hndl);
            //var_dump(curl_getinfo($this->hndl,  CURLINFO_HEADER_OUT));
            $this->getResponse()->setData($resp);
            return $this;
         }
         
      abstract public function getResponse(): IResponse;
   }

