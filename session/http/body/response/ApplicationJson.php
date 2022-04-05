<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body\Response as ResponseBase;


/**
 * PHP's json_decode() only works with UTF-8
 */
class ApplicationJson extends ResponseBase
   {
      public function getData(): ?array
         {
            return json_decode(
               parent::getData(),
               flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY
            );
         }
   }
