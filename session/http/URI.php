<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\exception;


class URI
   {
      protected string  $scheme;
      
      protected string  $host;
      protected ?int    $port = null;
      
      protected string  $path = '/';
      protected ?string $query = null;
      
      
      
      public function __construct(string $uri)
         {
            //there are quite a lot of cases where parse_url() returns incorrect result
            //so using RegExp for spliting, no validation performed/intended here
         
            $m = null;
            if(!preg_match('/^(?:(https?):\/\/)?([^\s\/:]+?)(?::(\d+))?(\/[^?]*?)?(?:\?([^#]+))?(?:#.*)?$/ui', $uri, $m))
               throw new exception\UnexpectedValueException('Malformed URI given: '.$uri);
            
            $this->host = $m[2]; //at least 1 char is guaranteed by RE, treating as valid locally-defined (/etc/hosts)
            if(!empty($m[3])) $this->port = (int)$m[3];
            if(!empty($m[4])) $this->path = $this->_canonicalizePath($m[4]);
            if(!empty($m[5])) $this->query = $m[5];
            
            if(!empty($m[1])) $this->scheme = strtolower($m[1]);
            else $this->scheme = $this->port===443? 'https' : 'http';
         }
         
      
      /**
       * Replace path with the given one as-is if it is absolute
       * or with resolved one if given path is relative
       * 
       * @param string $pathMod  Absolute or relative path
       * @return self
       */
      final public function changePath(string $pathMod): self
         {
            if(!strlen($pathMod=trim($pathMod))) throw new exception\UnexpectedValueException('Empty path given');
            $this->path = $this->_canonicalizePath($pathMod[0]==='/'? $pathMod : $this->getPathDir().$pathMod);
            return $this;
         }
         
      //TODO: null|string|array type for PHP8+
      final public function setQuery($query): self
         {
            if(empty($query)) $this->query = null;
            else $this->query = is_array($query)? http_build_query($query) : $query;
            return $this;
         }
         
      //TODO: move to FS util
      private function _canonicalizePath(string $path, string $sep='/'): string
         {
            $parts = [];
            foreach(explode($sep, $path) as $part)
               {
                  if(empty($part) || ($part==='.')) continue;
                  
                  if($part !== '..') $parts[]=$part;
                  elseif(count($parts) > 0) array_pop($parts);
                  else throw new \UnexpectedValueException('Invalid path given');
               }

            return $sep.implode($sep, $parts);
         }
      
      
      final public function getHost(): string
         {
            return $this->host;
         }
         
      final public function getPath(): string
         {
            return $this->path;
         }
         
      final public function getPathDir(): string
         {
            return preg_replace('/\/[^\/]*$/', '', $this->getPath()).'/';
         }
         
      final public function isSecure(): bool
         {
            return strcasecmp($this->scheme, 'https') === 0;
         }
         
         
      final public function __toString(): string
         {
            return
               $this->scheme.'://'.
               $this->host.(empty($this->port)? '':':'.$this->port).
               $this->path.
               (empty($this->query)? '':'?'.$this->query);
         }
   }
