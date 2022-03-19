<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body\Response as ResponseBase;


class ApplicationOctetStream extends ResponseBase
   {
      public function setCharset(string $charset): static
         {
            //nothing, octet-stream can not have a charset
            return $this;
         }
   }
