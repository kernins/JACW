<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session;


class Config extends session\Config
   {
      protected const OPTS_DEFAULT = [
         CURLOPT_FOLLOWLOCATION     => true,    //FIXME: host may differ, and so eligible cookies
         CURLOPT_MAXREDIRS          => 5,
         CURLOPT_AUTOREFERER        => true,
         CURLOPT_UNRESTRICTED_AUTH  => false,   //default FALSE, affects Authorization: (7.58.0+) and Cookie: (7.64.0+)
         CURLOPT_DEFAULT_PROTOCOL   => 'http'
      ] + parent::OPTS_DEFAULT;
      
      /*protected const OPTS_LOCKED = [
         CURLOPT_HEADER          => false    //headers are meant to be handled by CURLOPT_HEADERFUNCTION
      ] + parent::OPTS_LOCKED;*/
      
      
      
      public function followLocation(bool $follow): self
         {
            $this->setOpt(CURLOPT_FOLLOWLOCATION, $follow);
            return $this;
         }
   }
