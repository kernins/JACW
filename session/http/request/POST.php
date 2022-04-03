<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


class POST extends http\Request
   {
      public function __construct(http\URI $url)
         {
            parent::__construct($url, 'POST');
         }
      
      
      
      public function setBody(http\body\RequestForm|http\body\RequestContent $body): self
         {
            $body instanceof http\body\RequestForm?
               $this->setBodyFormData($body) :
               $this->setBodyContent($body);

            return $this;
         }
         
      protected function setBodyFormData(http\body\RequestForm $body): self
         {
            $this->opts[\CURLOPT_POSTFIELDS] = $body->getPostableData();
            return $this;
         }
         
      protected function setBodyContent(http\body\RequestContent $body): self
         {
            $this->opts[\CURLOPT_POSTFIELDS] = $body->getPostableContent();
            $this->addHeaders(
               new http\headers\Request(['Content-Type' => $body->getContentType()])
            );
            return $this;
         }
   }
