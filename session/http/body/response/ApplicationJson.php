<?php
namespace lib\dp\Curl\session\http\body\response;


class ApplicationJson extends Text
   {
      public function getData()
         {
            return json_decode(parent::getData(), true, 512, JSON_THROW_ON_ERROR);
         }
   }
