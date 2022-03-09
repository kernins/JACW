<?php
namespace lib\dp\Curl\session\http\cookies;
use lib\dp\Curl\exception;


final class Cookie
   {
      private string $_name;
      private string $_value;
      
      /**
       * Scope specifier (domain + path + secure)
       * @var ScopeSpec
       */
      private ?ScopeSpec $_scopeSpec = null;
      private ?\DateTimeImmutable $_validUntil = null;
      
      
      
      public function __construct(string $name, string $value, ?ScopeSpec $scope=null)
         {
            $this->_name = $name;
            $this->_value = $value;
            if(!empty($scope)) $this->setScope($scope);
         }
      
      
      public function setScope(ScopeSpec $scope): self
         {
            $this->_scopeSpec = $scope;
            return $this;
         }
      
      public function setValidUntil(\DateTimeInterface $vu): self
         {
            $this->_validUntil = $vu instanceof \DateTimeImmutable? $vu : \DateTimeImmutable::createFromMutable($vu);
            return $this;
         }
      
      
      public function isExpired(): bool
         {
            return !empty($this->_validUntil) && ($this->_validUntil < new \DateTime());
         }
      
      
      public function getIdent(): string
         {
            return $this->_name.(empty($this->_scopeSpec)? '':'@'.$this->_scopeSpec);
         }
   }
