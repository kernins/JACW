<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session\IResponse, lib\dp\Curl\exception;


Class Response implements IResponse
   {   
      /**
       * A stack of header lists from all responses in chronological order
       * There could be multiple in case of redirections with follow-location enabled
       * 
       * @var headers\Response[]
       */
      private array              $_headersStack = [];
      private cookies\Response   $_cookies;
      
      protected                  $data = null;
      
      
      
      public function __construct()
         {
            
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
