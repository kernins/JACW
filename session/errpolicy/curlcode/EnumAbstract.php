<?php
namespace lib\dp\Curl\session\errpolicy\curlcode;
use lib\dp\Curl\session\errpolicy\ICurlErrorCase;


//TODO: refactor to native enum php 8.1
abstract class EnumAbstract implements ICurlErrorCase
   {
      public int $value; //tmp impl to mimic native enums
      
      
      private function __construct(int $value)
         {
            $this->value = $value;
         }
   
   
      final public static function tryFrom(int $code): ?static
         {
            return in_array($code, static::cases())? new static($code) : null;
         }
         
      abstract public static function cases(): array;
   }

