<?php
namespace lib\dp\Curl\session\errpolicy;


/**
 * This is just a passable container
 * Should contain no logic, except convenience methods
 */
final class Error
   {
      private string                $_message;
      private int                   $_code;
      
      private string                $_throwableFQN;
      private int                   $_retriesAllowed;
      
      private ?\DateTimeImmutable   $_retryAfter = null;
      
      
      
      public function __construct(string $message, int $code, string $throwableFQN, int $retriesAllowed=0)
         {
            $this->_message = $message;
            $this->_code = $code;
            
            $this->_throwableFQN = $throwableFQN;
            $this->_retriesAllowed = $retriesAllowed;
         }
         
      public function setRetryAfter(\DateTimeInterface $dt): self
         {
            $this->_retryAfter = \DateTimeImmutable::createFromInterface($dt);
            return $this;
         }
      
      
      public function isRetryable(int $attempt): bool
         {
            return ($attempt <= $this->_retriesAllowed);
         }
         
      public function getRetryDelaySeconds(): ?int
         {
            return empty($this->_retryAfter)?
               null :
               max(0, $this->_retryAfter->getTimestamp() - time());
         }
      
      
      public function throw(): void //TODO: void->never for php 8.1+
         {
            throw new $this->_throwableFQN($this->_message, $this->_code);
         }
   }
