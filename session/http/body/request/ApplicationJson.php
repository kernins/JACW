<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body;


class ApplicationJson extends body\RequestContent
   {
      public function __construct($data)
         {
            parent::__construct('application/json', json_encode($data, JSON_THROW_ON_ERROR));
         }
   }
