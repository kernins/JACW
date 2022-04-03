<?php
namespace lib\dp\Curl\session\http\body;


class RequestContent
   {
      protected string  $contentType;
      protected string  $rawData;
      
      
      
      public function __construct(string $contentType, string $rawData)
         {
            $this->contentType = $contentType;
            $this->rawData = $rawData;
         }
      
      
      
      public function getContentType(): string
         {
            return $this->contentType;
         }
         
      public function getPostableContent(): string
         {
            return $this->rawData;
         }
   }
