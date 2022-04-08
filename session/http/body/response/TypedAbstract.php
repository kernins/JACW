<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body, lib\dp\Curl\exception;


abstract class TypedAbstract extends body\Response
   {
      public static function getHandleableContentType(): string
         {
            $cNameParts = preg_split(
               '/([A-Z])/',
               str_replace(__NAMESPACE__.'\\', '', static::class),
               -1,
               \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE
            );
            
            if(count($cNameParts) % 2) throw new exception\LogicException(
               'Invalid typed body class name: '.static::class
            );
            
            $parts = [];
            foreach(array_chunk($cNameParts, 2) as $p) $parts[] = strtolower($p[0]).$p[1];
            
            return array_shift($parts).'/'.(empty($parts)? '*' : implode('-', $parts));
         }
   }
