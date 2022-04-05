<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session;


class Handler extends session\HandlerAbstract
   {
      public function setConfig(session\Config $cfg): static
         {
            parent::setConfig(
               //casting to http\Config if necessary
               $cfg instanceof Config? $cfg : new Config($cfg->toArray())
            );
            return $this;
         }
      
      
      public function getConfig(): Config
         {
            return parent::getConfig();
         }
      
      public function getResponse(): ?Response
         {
            return parent::getResponse();
         }
      
      
      public function init(): static
         {
            parent::init();
            
            $this->setOpt(\CURLOPT_HEADERFUNCTION, function(\CurlHandle $cHndl, string $line) {
               $wLen = strlen($line); //orig data len
               if(strlen($line=trim($line)) > 0)
                  {
                     if(!$this->hasResponse()) $this->initResponse(); //JIT
                     
                     $m = null;
                     if(preg_match('/^HTTP\/([\d\.]{1,3})\s+(\d{3})/i', $line, $m))
                        {
                           $this->getResponse()->appendHeaders(
                              new headers\Response(
                                 new URI($this->infoProvider->getInfoEffectiveURL()),
                                 $m[1],
                                 $m[2]
                              )
                           );
                        }
                     else $this->getResponse()->getHeaders()->setFromHeaderLine($line);
                  }
               return $wLen;
            });
            
            //Default error policy
            if(empty($this->errorPolicy)) $this->errorPolicy = new ErrorPolicy();
            
            return $this;
         }
      
      protected function initResponse(): void
         {
            $this->response = new Response($this->infoProvider);
         }
      
      
      public function checkError(): ?session\errpolicy\Error
         {
            $err = parent::checkError();
            if(!empty($err) && !empty($ra=$this->getResponse()?->getHeaders()?->getRetryAfter()))
               {
                  //429 & 503 responses may be accompanied by Retry-After header
                  $err->setRetryAfter(
                     //adding small safety margin to compensate for possible clock drift
                     \DateTimeImmutable::createFromFormat('U', $ra->getTimestamp() + 1)
                  );
               }
            return $err;
         }
   }
