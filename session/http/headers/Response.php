<?php
namespace lib\dp\Curl\session\http\headers;
use lib\dp\Curl\session\http, lib\dp\Curl\exception;


final class Response extends BaseList
   {
      private http\URI  $_originURI;
      private string    $_protoVer;
      private int       $_statusCode;
      
      /**
       * As opposed to request, where only one Cookie header is allowed,
       * response may have multiple Set-Cookie lines, one per each cookie

       * @var array  Array of raw cookie specifications
       */
      private array     $_cookies = [];
      
      
      
      public function __construct(http\URI $originUri, string $protoVer, int $statusCode)
         {
            $this->_originURI = $originUri;
            $this->_protoVer = $protoVer;
            $this->_statusCode = $statusCode;
         }
      
   
      public function setFromHeaderLine(string $hdrLine): self
         {
            if(!strlen($hdrLine=trim($hdrLine))) throw new exception\UnexpectedValueException('Empty header line given');
            
            $m = null;
            if(!preg_match('/^([-\w.]+):\s*(.+)$/i', $hdrLine, $m)) throw new exception\UnexpectedValueException('Malformed header line given ['.$hdrLine.']');
            
            return $this->set($m[1], $m[2]);
         }
         
      public function set(string $name, string $value): self
         {
            if(strcasecmp($name, 'Set-Cookie') === 0) $this->_cookies[] = $value;
            else parent::set($name, $value);
            return $this;
         }
      
      
      public function getRedirLocation(): ?string
         {
            return ($this->_statusCode >= 300) && ($this->_statusCode < 400)? $this->get('Location') : null;
         }
         
      public function getRedirLocationURI(): ?http\URI
         {
            if(!empty($loc=$this->getRedirLocation()))
               {
                  //$loc should be standarts-conformant here, so parse_uri() should be fine
                  $locParts = parse_url($loc);
                  if(empty($locParts['scheme'])) //relative uri
                     {
                        $uri = clone $this->_originURI;
                        if(!empty($locParts['path'])) $uri->changePath($locParts['path']); //handle both absolute/relative
                        $uri->setQuery(empty($locParts['query'])? null : $locParts['query']);
                     }
                  else $uri = new http\URI($loc); //absolute uri
               }
            return empty($uri)? null : $uri;
         }
      
      
      /*public function getRawCookies(): array
         {
            return $this->_cookies;
         }*/
         
      public function getCookies(): http\cookies\Response
         {
            $cookies = new http\cookies\Response();
            if(!empty($this->_cookies))
               {
                  //default scope for cookies set w/o domain/path attrs, exactHost=true as per RFC6265#4.1.2.3
                  $dfltScope = http\cookies\ScopeSpec::createFromURI($this->_originURI, true);
                  foreach($this->_cookies as $cook) $cookies->setFromHeader($cook, $dfltScope);
               }
            return $cookies;
         }
   }