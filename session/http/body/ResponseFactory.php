<?php
namespace lib\dp\Curl\session\http\body;
use lib\dp\Curl\exception;


final class ResponseFactory
   {
      public static function newInstanceForContentType(string $ct, ?string $charset=null): Response
         {
            $ctParts = preg_split('/[\/-]/', $ct, -1, PREG_SPLIT_NO_EMPTY);
            if(count($ctParts) < 2) throw new exception\UnexpectedValueException('Invalid content-type given');
            
            //FIXME: php 8.1 replace 'ucfirst' to ucfirst(...)
            return class_exists($contentSpecificClass=__NAMESPACE__.'\\response\\'.implode('', array_map('ucfirst', $ctParts)))?
               new $contentSpecificClass($charset) :
               self::newInstanceGeneric($charset);
         }
         
      public static function newInstanceGeneric(?string $charset=null): Response
         {
            return new Response($charset);
         }
   }
