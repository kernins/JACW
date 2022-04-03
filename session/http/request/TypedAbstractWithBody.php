<?php
namespace lib\dp\Curl\session\http\request;
use lib\dp\Curl\session\http;


abstract class TypedAbstractWithBody extends TypedAbstract
   {
      use TWithBody;
      
      
      public function __construct(http\URI $url, http\body\RequestForm|http\body\RequestRaw|null $body=null)
         {
            parent::__construct($url);
            if(!empty($body)) $this->setBody($body);
         }
   }
