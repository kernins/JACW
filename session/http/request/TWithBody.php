<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


trait TWithBody
   {
      public function setBody(http\body\RequestForm|http\body\RequestRaw $body): static
         {
            $body instanceof http\body\RequestForm?
               $this->setBodyFormData($body) :
               $this->setBodyRawContent($body);

            return $this;
         }
      
      
      final protected function setBodyFormData(http\body\RequestForm $body): void
         {
            $this->setOptPostData($body->getFormData());
         }
      
      final protected function setBodyRawContent(http\body\RequestRaw $body): void
         {
            $this->setOptPostData($body->getContent());
            $this->addHeaders(
               new http\headers\Request(['Content-Type' => $body->getContentType()])
            );
         }
      
      final protected function setOptPostData($data): void
         {
            $this->setOpt(\CURLOPT_POSTFIELDS, $data);
         }
      
      
      abstract protected function addHeaders(http\headers\Request $headers);
      abstract protected function setOpt(int $opt, $value);
   }
