<?php
namespace lib\dp\Curl\session\http\cookies;
use lib\dp\Curl\exception;


final class Response extends BaseList
   {
      /**
       * Sets a cookie from Set-Cookie header value
       * 
       * @param string           $setCookieLine    Set-Cookie header value
       * @param ScopeSpec|null   $defaultScope     Will be cloned be4 use. Meant to be exactHost=true, but not enforced
       * @return self
       * @throws exception\UnexpectedValueException
       */
      public function setFromHeader(string $setCookieLine, ?ScopeSpec $defaultScope=null): self
         {
            //Cookie definition begins with a name-value pair
            //CookName (and any other attr-name) must not contain a CTL, whitespace and separator chars, e.g. () <> @ , ; : \ " / [] ? = {}
            //CookValue can optionally be wrapped in double quotes and include any US-ASCII chars except: CTL, whitespace and " , ; \
            //CookValue may contain = char
            
            $m = null;
            if(empty(preg_match_all('/([\w.-]+)\s*(?:=\s*([^;]+?)\s*)?(?=;|$)/i', $setCookieLine, $m)))
               throw new exception\UnexpectedValueException('Empty or malformed Set-Cookie line given: '.$setCookieLine);
            
            $cook = new Cookie(array_shift($m[1]), trim(array_shift($m[2]), '"'));
            
            $attrs = [];
            foreach($m[1] as $i=>$n) $attrs[strtolower($n)] = $m[2][$i];
            
            if(!empty($attrs['domain'])) $scope = new ScopeSpec($attrs['domain']);
            elseif(!empty($defaultScope)) $scope = clone $defaultScope;
            if(!empty($scope))
               {
                  if(!empty($attrs['path'])) $scope->setPath($attrs['path']);
                  if(array_key_exists('secure', $attrs)) $scope->setSecure(true);
                  $cook->setScope($scope);
               }
            
            if(isset($attrs['max-age'])) //Max-Age has precedence over Expires
               {
                  //a zero or negative number will expire the cookie immediately
                  if((int)$attrs['max-age'] == 0) $attrs['max-age'] = -1;
                  $validUntil = \DateTimeImmutable::createFromFormat('U', time() + (int)$attrs['max-age']);
               }
            elseif(!empty($attrs['expires'])) $validUntil = \DateTimeImmutable::createFromFormat(\DateTime::COOKIE, $attrs['expires']);
            //validUntil is not that important to throw an exception on malformed src values, just ignore them silently
            if(!empty($validUntil)) $cook->setValidUntil($validUntil);
            
            $this->set($cook);
            return $this;
         }
         
      /**
       * Sets a cookie from Netscape-formatted line (used by CURL's internal cookie engine)
       * 
       * @param string $cookieLine
       * @return self
       * @throws exception\UnexpectedValueException
       */
      public function setFromNetscape(string $cookieLine): self
         {
            if(count($cookDef=explode("\t", $cookieLine)) != 7)
               throw new exception\UnexpectedValueException('Invalid cookie line given: not in netscape format');
            
            $cook = new Cookie(
               $cookDef[5],
               $cookDef[6],
               new ScopeSpec(
                  preg_replace('/^#HttpOnly_/i', '', $cookDef[0]),
                  $cookDef[2],
                  strcasecmp($cookDef[3], 'true')===0,
                  strcasecmp($cookDef[1], 'false')===0
               )
            );
            if((int)$cookDef[4] > 0) $cook->setValidUntil(\DateTimeImmutable::createFromFormat('U', (int)$cookDef[4]));
            
            $this->set($cook);
            return $this;
         }
   }
