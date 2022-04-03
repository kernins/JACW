<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


class POST extends WithBody
   {
      public function __construct(http\URI $url, http\body\RequestForm|http\body\RequestRaw|null $body=null)
         {
            parent::__construct($url, 'POST', $body);
         }
   }
