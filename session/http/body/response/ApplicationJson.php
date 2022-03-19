<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body\Response as ResponseBase;


class ApplicationJson extends ResponseBase
   {
      public function getData()
         {
            return json_decode(parent::getData(), true, 512, JSON_THROW_ON_ERROR);
         }
   }
