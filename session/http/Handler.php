<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session;


class Handler extends session\HandlerAbstract
   {
      //protected Response $response;
   
      
      protected function initHandlers(): void
         {
            curl_setopt($this->hndl, CURLOPT_HEADERFUNCTION, function(\CurlHandle $cHndl, string $line) {
               if(empty($this->response)) $this->initResponse(); //JIT
               
               $dLen = strlen($line);
               if(strlen($line=trim($line)) > 0)
                  {
                     $m = null;
                     if(preg_match('/^HTTP\/([\d\.]{1,3})\s+(\d{3})/i', $line, $m))
                        {
                           $this->response->appendHeaders(
                              new headers\Response(
                                 new URI(curl_getinfo($this->hndl, CURLINFO_EFFECTIVE_URL)),
                                 $m[1],
                                 $m[2]
                              )
                           );
                        }
                     else $this->response->getHeaders()->setFromHeaderLine($line);
                  }
               return $dLen;
            });
            
            curl_setopt($this->hndl, CURLOPT_WRITEFUNCTION, function(\CurlHandle $hndl, $chunk){
               if(empty($this->response)) $this->initResponse(); //JIT
               
               $this->getResponse()->appendData($chunk);
               return strlen($chunk);
            });
            
            //TODO: refactor init* methods
            if(empty($this->errorPolicy)) $this->errorPolicy = new ErrorPolicy(); //default policy
         }
         
      protected function initResponse(): void
         {
            $this->response = new Response($this->infoProvider);
         }
      
      
      public function setConfig(session\Config $cfg): self
         {
            parent::setConfig(
               //casting to http\Config if necessary
               $cfg instanceof Config? $cfg : new Config($cfg->toArray())
            );
            return $this;
         }
   }
