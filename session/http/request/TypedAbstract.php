<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


abstract class TypedAbstract extends http\Request
   {
      public function __construct(http\URI $url)
         {
            parent::__construct(
               $url,
               str_replace(__NAMESPACE__.'\\', '', static::class)
            );
         }
   }
