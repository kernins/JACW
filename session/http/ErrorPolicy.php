<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session\errpolicy, lib\dp\Curl\exception;


class ErrorPolicy extends errpolicy\PolicyAbstract
   {
      //todo: declare final
      public const RCP_IGNORE_NONE        = 0;
      public const RCP_IGNORE_SERVERERR   = 0b10000000;
      public const RCP_IGNORE_CLIENTERR   = 0b00000001;
      public const RCP_IGNORE_RATELIMIT   = 0b00000010;
      
      
      protected int $respCodePolicy;
      
      protected int $serverErrorRetryDelay = 5;
      protected int $rateLimitCooldownDelay = 30;
      
      
      
      public function __construct(int $maxRetries=0, bool $respRequired=true, int $respCodePolicy=self::RCP_IGNORE_CLIENTERR)
         {
            parent::__construct($maxRetries, $respRequired);
            $this->respCodePolicy = $respCodePolicy;
         }
      
      
      public function setServerErrorRetryDelay(int $seconds): static
         {
            if($seconds < 0) throw new exception\InvalidArgumentException('RetryDelay may not be negative');
            $this->serverErrorRetryDelay = $seconds;
            return $this;
         }
      
      public function setRateLimitCooldownDelay(int $seconds): static
         {
            if($seconds < 0) throw new exception\InvalidArgumentException('RateLimit cooldown period may not be negative');
            $this->rateLimitCooldownDelay = $seconds;
            return $this;
         }
   
      
      public function evaluateResponseCode(int $respCode): ?errpolicy\Error
         {
            /* Treating 5xx errors as retryable, as it may be just an occasional/temporary fail
             * 4xx errors are non-retryable by definition, except 429 TooManyRequests
             */
            return match(true) {
               !($this->respCodePolicy & self::RCP_IGNORE_SERVERERR) && ($respCode>=500) =>
                  new errpolicy\Error('Server error '.$respCode, $respCode, exception\transfer\ServerErrorException::class, ...$this->getRetriesLimitAndDelayForServerError()),
               !($this->respCodePolicy & self::RCP_IGNORE_RATELIMIT) && ($respCode==429) =>
                  new errpolicy\Error('Server engaged rate-limiting', $respCode, exception\transfer\RateLimitException::class, ...$this->getRetriesLimitAndDelayForRateLimit()),
               !($this->respCodePolicy & self::RCP_IGNORE_CLIENTERR) && ($respCode>=400) && ($respCode<500) =>
                  new errpolicy\Error('Client error '.$respCode, $respCode, exception\transfer\ClientErrorException::class, 0),
               default => null
            };
         }
      
      /**
       * @return array  [int limit, int delaySeconds]
       */
      protected function getRetriesLimitAndDelayForServerError(): array
         {
            return [$this->maxRetriesAllowed, $this->serverErrorRetryDelay];
         }
         
      /**
       * @return array  [int limit, int delaySeconds]
       */
      protected function getRetriesLimitAndDelayForRateLimit(): array
         {
            return [$this->rateLimitCooldownDelay>0? 1:0, $this->rateLimitCooldownDelay];
         }
   }
