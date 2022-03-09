<?php
namespace lib\dp\Curl\session\http\cookies;


abstract class BaseList implements \IteratorAggregate
   {
      /** 
       * @var Cookie[]  ident => Cookie
       */
      private array $_list = [];
      
      
      public function set(Cookie $cook): self
         {
            $this->_list[$cook->getIdent()] = $cook;
            return $this;
         }
         
      public function merge(self $list): self
         {
            foreach($list as $cook) $this->set($cook);
            return $this;
         }
         
         
      /**
       * @return Cookie[]
       */
      public function getIterator(): \Generator
         {
            yield from $this->_list;
         }
   }
