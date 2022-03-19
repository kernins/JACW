<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session, lib\dp\Curl\exception;


Class Response implements session\IResponse
   {
      private session\InfoProvider  $_infoProvider;
      
      /**
       * A stack of header lists from all responses in chronological order
       * There could be multiple in case of redirections with follow-location enabled
       * 
       * @var headers\Response[]
       */
      private array                 $_headersStack = [];
      private cookies\Response      $_cookies;
      
      /**
       * Meant to be initialized just-in-time
       * Indicates actual presence of body in srv response (incompat with CURLOPT_HEADER)
       * @var body\Response|null
       */
      protected ?body\Response      $body = null;
      
      
      
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
         
      final public function appendData(string $chunk): self
         {
            if($this->body === null)
               {
                  /* NB: There is no guarantee headers will be present at this moment.
                   * Particulary when CURLOPT_HEADER option is set, headers will be prepended to body
                   * and so first call of this fn will be at the time first header line is received
                   */
                  if(!empty($ct=$this->getHeaders()?->getContentTypeAndCharset()))
                     $this->body = body\FactoryResponse::newInstanceForContentType(...$ct);
                  else $this->body = body\FactoryResponse::newInstanceGeneric();
               }
            $this->body->appendData($chunk);
            return $this;
         }
      
      
      final public function getStatusCode(): int
         {
            return $this->_infoProvider->get(CURLINFO_RESPONSE_CODE);
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
      
      
      final public function hasBody(string ...$ofType): bool
         {
            if(!empty($this->body))
               {
                  foreach($ofType as $t) {if($this->body instanceof $t) return true;}
                  return empty($ofType); //we still have a valid body if no type constraints specified
               }
            return false;
         }
      
      final public function getBody(): ?body\Response
         {
            return $this->body;
         }
   }
