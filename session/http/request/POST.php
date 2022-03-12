<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


class POST extends http\Request
   {
      public function __construct(http\URI $url)
         {
            parent::__construct($url, 'POST');
         }
         
         
      //FIXME: refactor all post* methods, don't use $this->opts
      public function postFormWwwUrlencoded($data): self //TODO: string|array typehind for php8
         {
            $this->opts[CURLOPT_POSTFIELDS] = is_array($data)? http_build_query($data) : $data;
            return $this;
         }
         
      public function postMultipartFormData(array $data): self
         {
            $this->opts[CURLOPT_POSTFIELDS] = $data;
            return $this;
         }
         
      //FIXME: ...and especially this one
      public function postRaw(string $data, string $contentType): self
         {
            $this->opts[CURLOPT_POSTFIELDS] = $data;
            $this->addHeaders(
               (new http\headers\Request())->set('Content-Type', $contentType)
            );
            return $this;
         }
         
      public function postRawJSON($data): self
         {
            return $this->postRaw(json_encode($data), 'application/json');
         }
   }
