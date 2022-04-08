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
      
      
      public function set(string $name, string $value): self
         {
            if(!strlen($value)) throw new exception\UnexpectedValueException('Empty value given for header '.$name);
            return parent::set($name, $value);
         }
      
      
      public function setContentType(string $ct): self
         {
            return $this->set('Content-Type', $ct);
         }
      
      public function setAccept(string $ct): self
         {
            return $this->set('Accept', $ct);
         }
      
      
      public function toArray(): array
         {
            $arr = [];
            foreach($this as $name=>$val) $arr[] = $name.': '.$val;
            return $arr;
         }
      
   }
