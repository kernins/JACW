<?php
namespace lib\dp\Curl\session\http\body;


class Response implements \Stringable
   {
      protected string  $raw = '';
      protected ?string $charset = null;
      
      
      
      final public function __construct(?string $charset=null)
         {
            if(!empty($charset)) $this->setCharset($charset);
         }
         
      public function setCharset(string $charset): static
         {
            $this->charset = $charset;
            return $this;
         }
      
      
      final public function setData(string $data): static
         {
            $this->raw = $data;
            return $this;
         }
         
      final public function appendData(string $chunk): static
         {
            $this->raw .= $chunk;
            return $this;
         }
      
      
      final public function isEmpty(): bool
         {
            return !strlen($this->raw);
         }
      
      final public function getDataRaw(): string
         {
            return $this->raw;
         }
         
      public function getData()
         {
            return $this->__toString();
         }
      
      
      final public function __toString(): string
         {
            return !empty($this->charset) && (strcasecmp($this->charset, 'utf8')!==0)?
               mb_convert_encoding($this->getDataRaw(), 'utf8', $this->charset) :
               $this->getDataRaw();
         }
   }
