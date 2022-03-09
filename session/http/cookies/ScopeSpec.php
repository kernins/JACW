<?php
namespace lib\dp\Curl\session\http\cookies;
use lib\dp\Curl\session\http, lib\dp\Curl\exception;


final class ScopeSpec
   {
      /**
       * Cookies set with Domain attr specified are valid not only for exactly that domain but also for all its subdomains
       * 
       * @var string Target domain
       */
      private string $_domain;
      /**
       * However cookies set w/o Domain attr are valid only for that specific host that set them
       * This prop is meant to indicate exactly such a case
       * 
       * @var bool   Require exact host match
       */
      private bool   $_exactHost = false;
      private string $_path = '/';
      
      private bool   $_secure = false;
   
      
      
      public function __construct(string $domain, ?string $path=null, bool $secure=false, bool $exactHost=false)
         {
            if(!strlen($domain=trim($domain))) throw new exception\UnexpectedValueException('Domain is required, empty string given');
            
            //as per RFC6265
            if($domain[-1] === '.') throw new exception\UnexpectedValueException('Invalid domain given: '.$domain);
            //TODO: keep domain intact to have cooks interchangeable with curl internal engine?
            $this->_domain = ltrim($domain, '.'); //leading dot (as per deprecated RFC2109) is ignored
            $this->_exactHost = $exactHost; //rename to includeSubdomains?
            
            if(!empty($path)) $this->_path = $path;
            $this->_secure = $secure;
         }
         
      /**
       * Primarily intended for default-scope generation
       * for cookies set w/o scope definition
       * 
       * @param http\URI   $uri
       * @param bool       $exactHost Defaults to TRUE, as cookies set w/o Domain scope are valid only for host that set them
       * @return self
       */
      public static function createFromURI(http\URI $uri, bool $exactHost=true): self
         {
            return new self(
               $uri->getHost(),
               $uri->getPathDir(),  //according to RFC6265#5.1.4
               false,               //non-secure
               $exactHost
            );
         }
      
      
      public function setPath(string $path): self
         {
            if(!strlen($path)) throw new exception\UnexpectedValueException('Path can not be empty');
            $this->_path = $path;
            return $this;
         }
         
      public function setSecure(bool $secure): self
         {
            $this->_secure = $secure;
            return $this;
         }
      
      
      public function getDomain(): string
         {
            return $this->_domain;
         }
         
      public function getPath(): string
         {
            return $this->_path;
         }
      
      
      public function __toString(): string
         {
            return ($this->_exactHost? '':'.').$this->_domain.$this->_path;
         }
   }
