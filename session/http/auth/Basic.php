<?php
namespace lib\dp\Curl\session\http\auth;
use lib\dp\Curl\session\http\IAuth;


class Basic implements IAuth
   {
      protected string $username;
      protected string $password;
      
      
      
      public function __construct(string $user, string $passwd)
         {
            //TODO: validation
            $this->username = $user;
            $this->password = $passwd;
         }
      
      
      public function toArray(): array
         {
            return [
               CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
               CURLOPT_USERNAME => $this->username,
               CURLOPT_PASSWORD => $this->password
            ];
         }
   }
