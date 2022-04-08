<?php
namespace lib\dp\Curl\session;


interface IResponse
   {
      public function appendData(string $chunk): self;
      
      
      public function getStatusCode(): int;
      
      public function getBody();
      public function hasBody(): bool;
   }
