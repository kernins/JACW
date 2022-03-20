<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session\errpolicy, lib\dp\Curl\exception;


class ErrorPolicy extends errpolicy\PolicyAbstract
   {
      public function evaluateResponseCode(int $respCode): ?errpolicy\Error
         {
            /* Leaving 4xx errors to application to handle
             * Treating 5xx errors as retryable, as it may be just occasional/temporary fail
             */
            return ($respCode >= 500)?
               new errpolicy\Error($respCode, 'Server returned error '.$respCode, exception\transfer\ServerErrorException::class, $this->maxRetriesAllowed) :
               null;
         }
   }
