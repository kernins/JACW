<?php
namespace lib\dp\Curl\session\http\body;
use lib\dp\Curl\exception;


class RequestRaw
   {
      protected string  $content;
      protected string  $contentType;
      
      
      
      public function __construct(string $content, string $contentType)
         {
            if(!strlen($content)) throw new exception\UnexpectedValueException('No body content given');
            if(!strlen($contentType)) throw new exception\UnexpectedValueException('No body content type specified');
            
            $this->content = $content;
            $this->contentType = $contentType;
         }
      
      
      
      public function getContent(): string
         {
            return $this->content;
         }
      
      public function getContentType(): string
         {
            return $this->contentType;
         }
   }
