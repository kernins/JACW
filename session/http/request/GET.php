<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


class GET extends http\Request
   {
      public function __construct(http\URI $url)
         {
            parent::__construct($url, 'GET');
         }
   }