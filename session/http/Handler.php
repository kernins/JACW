<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session;


class Handler extends session\HandlerAbstract
   {
      protected Response $response;
   
      
      protected function initHandlers(): void
         {
            $this->response = new Response();
            curl_setopt($this->hndl, CURLOPT_HEADERFUNCTION, function($cHndl, string $line) {
               $dLen = strlen($line);
               if(strlen($line=trim($line)) > 0)
                  {
                     $m = null;
                     if(preg_match('/^HTTP\/([\d\.]{1,3})\s+(\d{3})/i', $line, $m))
                        {
                           //var_dump(curl_getinfo($this->_hndl, CURLINFO_COOKIELIST), curl_getinfo($this->_hndl, CURLINFO_HEADER_OUT));
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
         }
         
      public function getResponse(): Response
         {
            return $this->response;
         }
         
      
   }
