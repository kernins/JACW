<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session\errpolicy, lib\dp\Curl\exception;


class ErrorPolicy extends errpolicy\PolicyAbstract
   {
      public const RCP_IGNORE_CLIENTERR   = 0b00000001;
      public const RCP_IGNORE_SERVERERR   = 0b00000010;
      
      protected int $respCodePolicy;
      
      
      
      public function __construct(int $maxRetries=0, bool $respRequired=true, int $respCodePolicy=self::RCP_IGNORE_CLIENTERR)
         {
            parent::__construct($maxRetries, $respRequired);
            $this->respCodePolicy = $respCodePolicy;
         }
   
      
      public function evaluateResponseCode(int $respCode): ?errpolicy\Error
         {
            /* Treating 5xx errors as retryable, as it may be just an occasional/temporary fail
             * 4xx errors are non-retryable by definition
             */
            return match(true) {
               !($this->respCodePolicy & self::RCP_IGNORE_SERVERERR) && ($respCode>=500) =>
                  new errpolicy\Error('Server error '.$respCode, $respCode, exception\transfer\ServerErrorException::class, $this->maxRetriesAllowed),
               !($this->respCodePolicy & self::RCP_IGNORE_CLIENTERR) && ($respCode>=400) && ($respCode<500) =>
                  new errpolicy\Error('Client error '.$respCode, $respCode, exception\transfer\ClientErrorException::class, 0),
               default => null
            };
         }
   }
