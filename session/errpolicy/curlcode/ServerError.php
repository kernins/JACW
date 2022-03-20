<?php
namespace lib\dp\Curl\session\errpolicy\curlcode;


final class ServerError extends EnumAbstract
   {
      public static function cases(): array
         {
            return [ //commented out ones are undef in php (tested 8.1)
            // CURLE_RANGE_ERROR,         //server does not support or accept range requests
               CURLE_TOO_MANY_REDIRECTS,  //when CURLOPT_FOLLOWLOCATION enabled
               CURLE_HTTP_RETURNED_ERROR  //HTTP code  >=400, requires CURLOPT_FAILONERROR
            ];
         }
   }
