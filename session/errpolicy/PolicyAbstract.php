<?php
namespace lib\dp\Curl\session\errpolicy;
use lib\dp\Curl\session, lib\dp\Curl\exception;


abstract class PolicyAbstract
   {
      protected const CURL_ERROR_DEFAULT  =  exception\transfer\CurlErrorException::class;
   
      protected const CURL_ERRORS_MAP = [
         curlcode\NetworkError::class     => exception\transfer\NetworkErrorException::class,
         curlcode\ProtocolError::class    => exception\transfer\ProtocolErrorException::class,
         curlcode\SSLError::class         => exception\transfer\SSLErrorException::class,
         curlcode\ServerError::class      => exception\transfer\ServerErrorException::class
      ];
      
      protected const CURL_ERRORS_RETRYABLE = [
         curlcode\NetworkError::class,
         curlcode\ProtocolError::class
      ];
      
      
      protected int $maxRetriesAllowed;
      
      
      
      public function __construct(int $maxRetries=0)
         {
            $this->maxRetriesAllowed = $maxRetries;
         }
      
      
      
      final public function evaluate(session\InfoProvider $sessIP): ?Error
         {
            //trying responseCode first as it is proto-specific and can produce more specific/narrow error-info
            if(empty($err=$this->evaluateResponseCode($sessIP->getInfoRespCode())) && $sessIP->hasPendingError())
               {
                  //evaluating libcurl error
                  $errCode = $sessIP->getLastErrorCode();
                  if(!empty($ec=$this->getKnownErrorCaseForCode($errCode)))
                     $err = new Error($errCode, $sessIP->getLastErrorMessage(), static::CURL_ERRORS_MAP[$ec::class], $this->getMaxRetriesAllowedForError($ec));
                  elseif(!empty(static::CURL_ERROR_DEFAULT))
                     $err = new Error($errCode, $sessIP->getLastErrorMessage(), static::CURL_ERROR_DEFAULT);
               }
            return $err;
         }
      
      /*
       * The concept of response-code is applicable to multiple protos (e.g. http, ftp, smtp)
       * Exact values and their meanings are protocol specific however
       * 
       * @param int $respCode
       * @return Error|null
       */
      abstract protected function evaluateResponseCode(int $respCode): ?Error;
      
      
      protected function getKnownErrorCaseForCode(int $code): ?ICurlErrorCase
         {
            $case = null;
            foreach(array_keys(static::CURL_ERRORS_MAP) as $enum)
               {
                  /* @var $enum ICurlErrorCase */
                  if(!empty($case=$enum::tryFrom($code))) break;
               }
            return $case;
         }
         
      protected function getMaxRetriesAllowedForError(ICurlErrorCase $err): int
         {
            return in_array($err::class, static::CURL_ERRORS_RETRYABLE)? $this->maxRetriesAllowed : 0;
         }
   }
