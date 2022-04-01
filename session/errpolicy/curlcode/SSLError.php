<?php
namespace lib\dp\Curl\session\errpolicy\curlcode;


final class SSLError extends EnumAbstract
   {
      public static function cases(): array
         {
            return [ //commented out ones are undef in php (tested 8.1)
               \CURLE_SSL_CONNECT_ERROR,         //problem somewhere in the SSL/TLS handshake, could be certs, passwords, etc
            // \CURLE_PEER_FAILED_VERIFICATION,  //failed to verify server's cert
            // \CURLE_SSL_ISSUER_ERROR,          //Issuer check failed
            // \CURLE_SSL_INVALIDCERTSTATUS,     //Status returned failure when asked with CURLOPT_SSL_VERIFYSTATUS
            // \CURLE_SSL_CLIENTCERT             //SSL Client Certificate required
            ];
         }
   }
