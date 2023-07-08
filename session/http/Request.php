<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\exception, lib\dp\Curl\session\IRequest;


class Request implements IRequest
   {
      protected URI              $url;
      protected string           $method;
      protected headers\Request  $headers;
      
      //TODO: remove? or replace with base Config inst?
      protected array            $opts = [];
      
      
      public function __construct(URI $url, string $method)
         {
            //TODO: replace string with enum?
            if(!strlen($method)) throw new exception\UnexpectedValueException('No request method (verb) specified');
         
            $this->url = $url;
            $this->method = $method;
            //TODO: method validation and normalization
            //TODO: switch to php 8.1 Enum later
         }
      
      
      public function setHeaders(headers\Request $headers): static
         {
            $this->headers = $headers;
            return $this;
         }
         
      public function addHeaders(headers\Request $headers): static
         {
            if(empty($this->headers)) $this->headers = clone $headers;
            else $this->headers->merge($headers);
            return $this;
         }
         
      
      public function setAuth(IAuth $auth): static
         {
            //TODO: refactor, don't use opts?
            $this->mergeOpts($auth->toArray());
            return $this;
         }
         
      final protected function mergeOpts(array $opts): void
         {
            $this->opts = $opts + $this->opts;
         }
         
      final protected function setOpt(int $opt, $value): void
         {
            $this->opts[$opt] = $value;
         }
      
      
      public function getMethod(): string
         {
            return $this->method;
         }
      
      public function getURI(): URI
         {
            return $this->url;
         }
      
      public function toArray(): array
         {
            $opts = [\CURLOPT_URL => (string)$this->url];
            
            //using dedicated opt for method (when available)
            //to ensure libcurl's internal consistency
            if(strcasecmp($this->method, 'GET') === 0)
               {
                  $opts[\CURLOPT_HTTPGET] = true;
               }
            elseif(strcasecmp($this->method, 'POST') === 0)
               {
                  $opts[\CURLOPT_POST] = true;
               }
            elseif(strcasecmp($this->method, 'HEAD') === 0)
               {
                  $opts[\CURLOPT_NOBODY] = true;
               }
            else $opts[\CURLOPT_CUSTOMREQUEST] = $this->method;
            
            if(!empty($this->headers)) $opts[\CURLOPT_HTTPHEADER] = $this->headers->toArray();
            
            return $opts + $this->opts;
         }
      
      
      /**
       * Meant primarily for logging
       * @return string
       */
      public function __toString(): string
         {
            //not using (string)$this->url to avoid query string
            //return $this->method.' http'.($this->url->isSecure()? 's':'').'://'.$this->url->getHost().$this->url->getPath();
            return $this->method.' '.$this->url; //with query string
         }
   }
