<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session\IRequest;


class Request implements IRequest
   {
      protected URI     $url;
      protected string  $method = 'GET';
      protected array   $opts = [];
      
      
      public function __construct(URI $url, ?string $method=null)
         {
            $this->url = $url;
            if(!empty($method)) $this->method = $method;
         }
         
         
      public function getURI(): URI
         {
            return $this->url;
         }
      
      public function toArray(): array
         {
            return [
                CURLOPT_CUSTOMREQUEST => $this->method,
                CURLOPT_URL => (string)$this->url
            ] + $this->opts;
         }
   }
