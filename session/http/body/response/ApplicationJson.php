<?php
namespace lib\dp\Curl\session\http\body\response;


/**
 * PHP's json_decode() only works with UTF-8
 */
class ApplicationJson extends TypedAbstract
   {
      public function getData()
         {
            return json_decode(
               parent::getData(),
               flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY
            );
         }
   }
