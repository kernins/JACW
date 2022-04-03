<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body;


class FormDataUrlencoded extends body\RequestForm
   {
      final public function getPostableData(): string
         {
            return http_build_query(parent::getPostableData());
         }
   }
