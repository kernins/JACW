<?php
namespace lib\dp\Curl\session\http\body\request;
use lib\dp\Curl\session\http\body, lib\dp\Curl\exception;


class ApplicationJson extends body\RequestRaw
   {
      public function __construct($data)
         {
            try
               {
                  parent::__construct(
                     json_encode($data, \JSON_THROW_ON_ERROR),
                     'application/json'
                  );
               }
            catch(\JsonException $ex)
               {
                  throw new exception\UnexpectedValueException(
                     'Failed to json_encode() given data', 
                     previous: $ex
                  );
               }
         }
   }
