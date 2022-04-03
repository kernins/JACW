<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body;


final class FormDataMultipart extends body\RequestForm
   {
      public function getFormData(): array
         {
            return parent::getFormData();
         }
   }

