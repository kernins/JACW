<?php
namespace lib\dp\Curl\session;


abstract class HandlerAbstract
   {
      protected $hndl = null;
      
      protected Config     $config;
      protected IRequest   $request;
   
      
      
      public function __construct(?IRequest $request=null, ?Config $config = null)
         {
            if($request !== null) $this->request = $request;
            if($config !== null) $this->config = $config;
         }
         
      public function __destruct()
         {
            if($this->hndl !== null) curl_close($this->hndl);
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
         
         
      public function exec()
         {
            $resp = curl_exec($this->hndl);
            //check success, check file
            $this->getResponse()->setData($resp);
            //var_dump(curl_getinfo($this->hndl, CURLINFO_COOKIELIST), curl_getinfo($this->hndl, CURLINFO_HEADER_OUT));
            
            /*$cooks=new http\cookies\Response();
            foreach(curl_getinfo($this->hndl, CURLINFO_COOKIELIST) as $c) $cooks->setFromNetscape($c);
            var_dump($cooks);*/
            
            return $this->getResponse();
         }
         
      abstract public function getResponse(): IResponse;
   }

