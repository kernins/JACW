<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\exception, lib\dp\Curl\session\IRequest;


class Request implements IRequest
   {
      protected URI              $url;
      protected string           $method = 'GET';
      protected headers\Request  $headers;
      
      //TODO: remove? or replace with base Config inst?
      protected array            $opts = [];
      
      
      public function __construct(URI $url, ?string $method=null)
         {
            $this->url = $url;
            if(!empty($method)) $this->method = $method;
            //TODO: method validation and normalization
            //TODO: switch to php 8.1 Enum later
         }
      
      
      public function setHeaders(headers\Request $headers): self
         {
            $this->headers = $headers;
            return $this;
         }
         
      public function addHeaders(headers\Request $headers): self
         {
            if(empty($this->headers)) $this->headers = clone $headers;
            else $this->headers->merge($headers);
            return $this;
         }
         
         
      //TODO: refactor to separate classes?
      public function setAuth(string $type, string ...$data): self
         {
            //TODO: refactor, don't use opts?
            switch(strtolower($type))
               {
                  case 'basic':
                     //CURLOPT_USERPWD is deprecated, and prevents use of ':' in passwd
                     if(count($data) != 2) throw new exception\UnexpectedValueException(
                        'Basic auth requires both username & password to be specified as separate args'
                     );
                     $this->opts = [
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_USERNAME => $data[0],
                        CURLOPT_PASSWORD => $data[1]
                     ] + $this->opts;
                     break;
                  case 'bearer':
                     $this->opts[CURLOPT_HTTPAUTH] = CURLAUTH_BEARER;
                     $this->opts[CURLOPT_XOAUTH2_BEARER] = $data[0];
                     break;
                  default:
                     throw new exception\UnexpectedValueException('Unknown auth method: '.$type);
               }
            return $this;
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
            $opts = [CURLOPT_URL => (string)$this->url];
            
            //using dedicated opt for method (when available)
            //to ensure libcurl's internal consistency
            if(strcasecmp($this->method, 'GET') === 0)
               {
                  $opts[CURLOPT_HTTPGET] = true;
               }
            elseif(strcasecmp($this->method, 'POST') === 0)
               {
                  $opts[CURLOPT_POST] = true;
               }
            elseif(strcasecmp($this->method, 'HEAD') === 0)
               {
                  $opts[CURLOPT_NOBODY] = true;
               }
            else $opts[CURLOPT_CUSTOMREQUEST] = $this->method;
            
            if(!empty($this->headers)) $opts[CURLOPT_HTTPHEADER] = $this->headers->toArray();
            
            return $opts + $this->opts;
         }
      
      
      /**
       * Meant primarily for logging
       * @return string
       */
      public function __toString(): string
         {
            //not using (string)$this->url to avoid query string
            return $this->method.' http'.($this->url->isSecure()? 's':'').'://'.$this->url->getHost().$this->url->getPath();
         }
   }
