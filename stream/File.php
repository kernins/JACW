<?php
namespace lib\dp\Curl\stream;
use lib\dp\Curl\IStream, lib\dp\Curl\exception;


class File implements IStream
   {
      protected resource $hndl;
      
      
      public function __construct(string $path, string $mode)
         {
            //TODO: validation and safeguards
            if(($h=fopen($path, $mode)) === false) throw new exception\RuntimeException('Failed to open '.$path.' in '.$mode);
            $this->hndl = $h;
         }
      
      public function __destruct()
         {
            fclose($this->hndl);
         }
      
      
      public function getHandle(): resource
         {
            return $this->hndl;
         }
   }
