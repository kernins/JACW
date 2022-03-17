<?php
namespace lib\dp\Curl\session;


interface IResponse
   {
      public function setData($data): self;
      public function appendData(string $chunk): self;
      
      
      public function getStatusCode(): int;
      
      public function getData();
      public function getDataRaw(): ?string;
   }
