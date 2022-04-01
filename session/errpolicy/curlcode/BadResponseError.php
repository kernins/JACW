<?php
namespace lib\dp\Curl\session\errpolicy\curlcode;


final class BadResponseError extends EnumAbstract
   {
      public static function cases(): array
         {
            return [ //commented out ones are undef in php (tested 8.1)
               \CURLE_WEIRD_SERVER_REPLY,  //server sent data libcurl could not parse
               \CURLE_GOT_NOTHING          //Nothing was returned from the server
            ];
         }
   }
