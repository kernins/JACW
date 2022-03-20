<?php
namespace lib\dp\Curl\session\errpolicy;


/**
 * Marker iface for curlcode enums
 */
interface ICurlErrorCase
   {
      public static function tryFrom(int $code): ?static;
   }
