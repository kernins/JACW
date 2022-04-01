<?php
namespace lib\dp\Curl\session\errpolicy\curlcode;


final class NetworkError extends EnumAbstract
   {
      public static function cases(): array
         {
            return [
               \CURLE_OPERATION_TIMEOUTED,
               \CURLE_COULDNT_CONNECT,
               \CURLE_COULDNT_RESOLVE_PROXY,
               \CURLE_COULDNT_RESOLVE_HOST,
               \CURLE_SEND_ERROR,             //failed sending network data
               \CURLE_RECV_ERROR              //failed receiving network data
            ];
         }
   }
