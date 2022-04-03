<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body;


final class FormDataUrlencoded extends body\RequestForm
   {
      public function getFormData(): string
         {
            return http_build_query(parent::getFormData());
         }
   }
