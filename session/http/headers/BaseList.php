<?php
namespace lib\dp\Curl\session\http\headers;


abstract class BaseList
   {
      private array $_list = [];
      
      
      public function set(string $name, string $value): self
         {
            $this->_list[strtolower($name)] = $value;
            return $this;
         }
         
      public function get(string $name): ?string
         {
            return array_key_exists($n=strtolower($name), $this->_list)? $this->_list[$n] : null;
         }
   }
