<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body\Request as RequestBase;


class ApplicationJson extends RequestBase
   {
      public function __construct($data)
         {
            parent::__construct('application/json', json_encode($data, JSON_THROW_ON_ERROR));
         }
   }
