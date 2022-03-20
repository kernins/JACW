<?php
namespace lib\dp\Curl\session\errpolicy;


/**
 * This is just passable container
 * Should contain no logic, except convenience methods
 */
final class Error
   {
      private int    $_code;
      private string $_message;
      
      private string $_throwableFQN;
      
      private int    $_retriesAllowed;
      
      
      
      public function __construct(int $code, string $message, string $throwableFQN, int $retriesAllowed=0)
         {
            $this->_code = $code;
            $this->_message = $message;
            
            $this->_throwableFQN = $throwableFQN;
            $this->_retriesAllowed = $retriesAllowed;
         }
      
      
      public function isRetryable(int $attempt): bool
         {
            return ($attempt <= $this->_retriesAllowed);
         }
      
      
      public function throw(): void //TODO: void->never for php 8.1+
         {
            throw new $this->_throwableFQN($this->_message, $this->_code);
         }
   }
