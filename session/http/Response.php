<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session, lib\dp\Curl\exception;


Class Response implements session\IResponse
   {   
      /**
       * A stack of header lists from all responses in chronological order
       * There could be multiple in case of redirections with follow-location enabled
       * 
       * @var headers\Response[]
       */
      private array                 $_headersStack = [];
      private cookies\Response      $_cookies;
      
      protected                     $data = null;
      
      private session\InfoProvider  $_infoProvider;
      
      
      
      public function __construct(session\InfoProvider $infoProvider)
         {
            $this->_infoProvider = $infoProvider;
         }
      
      
      /**
       * Append HTTP headers list received in latest response to headers stack
       * 
       * @param HeaderList $headers
       * @return self
       */
      final public function appendHeaders(headers\Response $headers): self
         {
            $this->_headersStack[] = $headers;
            return $this;
         }
         
      public function setData($data): self
         {
            $this->data = $data;
            return $this;
         }
         
      public function appendData(string $chunk): self
         {
            if($this->data === null) $this->data = $chunk;
            else $this->data .= $chunk;
            return $this;
         }
      
      
      public function getStatusCode(): int
         {
            return $this->_infoProvider->get(CURLINFO_RESPONSE_CODE);
         }
      
      
      //TODO: refactor
      final public function getData()
         {
            //TODO: use null-safe for php8
            if(!empty($data=$this->data) && !empty($hdrs=$this->getHeaders()) && !empty($ct=$hdrs->getContentType()))
               {
                  if(strncasecmp($ct, 'application/json', 16) === 0) $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
               }
            return $data;
         }
      
      final public function getDataRaw(): ?string
         {
            return $this->data;
         }
         
      final public function getHeaders(?int $idx = null): ?headers\Response
         {
            if($idx === null) $idx = count($this->_headersStack) - 1; //returning latest by dflt
            elseif($idx < 0) throw new exception\OutOfRangeException('HeadersStack index must be >= 0, '.$idx.' given');
            
            return isset($this->_headersStack[$idx])? $this->_headersStack[$idx] : null;
         }
         
      final public function getCookies(): cookies\Response
         {
            if(empty($this->_cookies))
               {
                  $this->_cookies = new cookies\Response();
                  foreach($this->_headersStack as $hdrLst) $this->_cookies->merge($hdrLst->getCookies());
                  
                  //default scope for cookies set w/o domain/path attrs, exactHost=true as per RFC6265#4.1.2.3
                  //FIXME: redirection cases, URI will differ
                  /*$dfltScope = cookies\ScopeSpec::createFromURI($this->request->getURI(), true);
                  foreach($this->_headersStack as $hdrLst)
                     {
                        foreach($hdrLst->getRawCookies() as $rc)
                           $this->_cookies->setFromHeader($rc, $dfltScope); //$dfltScope won't be mutated here
                     }*/
               }
            return $this->_cookies;
         }
   }
