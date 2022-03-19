<?php
namespace lib\dp\Curl\session\http\body;


class Response implements \Stringable
   {
      protected string $raw = '';
      
      
      
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
      
      /**
       * Returns raw data as-received
       * @return string
       */
      final public function getDataRaw(): string
         {
            return $this->raw;
         }
      
      /**
       * Returns (potentially filtered/decoded/preprocessed) data as string
       * @return string
       */
      public function getDataString(): string
         {
            return $this->getDataRaw();
         }
      
      /**
       * Returns data as appropriate type
       * @return mixed
       */
      public function getData()
         {
            return $this->getDataString();
         }
      
      
      final public function __toString(): string
         {
            return $this->getDataString();
         }
   }
