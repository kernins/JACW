<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


class POST extends http\Request
   {
      public function __construct(http\URI $url)
         {
            parent::__construct($url, 'POST');
         }
      
      
      //FIXME: refactor all set* methods, don't use $this->opts, merge into single method?
      public function setFormdataUrlencoded(string|array $data): self
         {
            $this->opts[\CURLOPT_POSTFIELDS] = is_array($data)? http_build_query($data) : $data;
            return $this;
         }
      
      public function setFormdataMultipart(array $data): self
         {
            $this->opts[\CURLOPT_POSTFIELDS] = $data;
            return $this;
         }
      
      public function setBody(http\body\Request $body): self
         {
            $this->opts[\CURLOPT_POSTFIELDS] = (string)$body;
            $this->addHeaders(
               (new http\headers\Request())->set('Content-Type', $body->getContentType())
            );
            return $this;
         }
   }
