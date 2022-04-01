<?php
namespace lib\dp\Curl\session\errpolicy\curlcode;


final class ProtocolError extends EnumAbstract
   {
      public static function cases(): array
         {
            return [ //commented out ones are undef in php (tested 8.1)
            // \CURLE_HTTP2,               //A problem was detected in the HTTP2 framing layer
            // \CURLE_HTTP2_STREAM,        //Stream error in the HTTP/2 framing layer
            // \CURLE_HTTP3,               //A problem was detected in the HTTP/3 layer
            // \CURLE_QUIC_CONNECT_ERROR   //QUIC is the protocol used for HTTP/3 transfers, may be caused by an SSL library error
            ];
         }
   }
