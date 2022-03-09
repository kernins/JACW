<?php
namespace lib\dp\Curl;


/**
 * Auxiliary stream iface
 * Meant to be a wrapper for aux streams to be passed to Curl
 * See CURLOPT_FILE / CURLOPT_INFILE / CURLOPT_WRITEHEADER/ CURLOPT_STDERR
 */
interface IStream
   {
      public function getHandle(): resource;
   }
