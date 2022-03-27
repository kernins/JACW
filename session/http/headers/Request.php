<?php
namespace lib\dp\Curl\session\http\headers;
use lib\dp\Curl\exception;


final class Request extends BaseList
   {
      public function __construct(array $headers=[])
         {
            foreach($headers as $name => $val)
               {
                  if(is_int($name)) throw new exception\InvalidArgumentException(
                     'Headers list must be in [name => value] form'
                  );
                  
                  $this->set($name, $val);
               }
         }
      
      
      public function toArray(): array
         {
            $arr = [];
            foreach($this as $name=>$val) $arr[] = $name.': '.$val;
            return $arr;
         }
      
   }
