<?php
namespace lib\dp\Curl\session\http\auth;
use lib\dp\Curl\session\http\IAuth;


class OAuth implements IAuth
   {
      protected string $token;
      
      
      
      public function __construct(string $token)
         {
            $this->token = $token;
         }
      
      
      public function toArray(): array
         {      
            return [
               CURLOPT_HTTPAUTH        => CURLAUTH_BEARER,
               CURLOPT_XOAUTH2_BEARER  => $this->token
            ];
         }
   }
