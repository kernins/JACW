<?php
namespace lib\dp\Curl\session\http\headers;
use lib\dp\Curl\exception;


final class Request extends BaseList
   {
      public function toArray(): array
         {
            $arr = [];
            foreach($this as $name=>$val) $arr[] = $name.': '.$val;
            return $arr;
         }
      
   }
