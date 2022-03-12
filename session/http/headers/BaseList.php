<?php
namespace lib\dp\Curl\session\http\headers;


abstract class BaseList implements \IteratorAggregate
   {
      private array $_list = [];
      
      
      public function set(string $name, string $value): self
         {
            //FIXME: remove strtolower() normalization from here
            //Request headers should retain their Canonical-Form
            $this->_list[strtolower($name)] = $value;
            return $this;
         }
      
      public function merge(self $lst): self
         {
            foreach($lst as $name=>$val) $this->set($name, $val);
            return $this;
         }
      
      
      public function get(string $name): ?string
         {
            return array_key_exists($n=strtolower($name), $this->_list)? $this->_list[$n] : null;
         }
         
      public function getContentType(): ?string
         {
            return $this->get('Content-Type');
         }
      
      
      public function getIterator(): \Generator
         {
            yield from $this->_list;
         }
   }
