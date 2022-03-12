<?php
namespace lib\dp\Curl\session;


class InfoProvider
   {
      private $_hndl;
      
      
      //TODO: CurlHandle typehint for php8
      public function __construct($curlHandle)
         {
            $this->_hndl = $curlHandle;
         }
         
         
      public function get(int $opt)
         {
            return curl_getinfo($this->_hndl, $opt);
         }
   }
