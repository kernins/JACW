<?php
namespace lib\dp\Curl\session\http\body;


interface ITranscodable
   {
      public function setCharset(string $charset): static;
   }
