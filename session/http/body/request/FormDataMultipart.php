<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body;


class FormDataMultipart extends body\RequestForm
   {
      final public function getPostableData(): array
         {
            return parent::getPostableData();
         }
   }

