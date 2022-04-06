<?php
namespace lib\dp\Curl\session\http\body;


final class FactoryResponse extends FactoryAbstract
   {
      public static function newInstanceForContentType(string $ct, ?string $charset=null): Response
         {
            $clsFQN=self::getImplFQNForContentType('response', $ct);
            $inst = empty($clsFQN)? self::newInstanceGeneric() : new $clsFQN();
            
            if(!empty($charset) && ($inst instanceof ITranscodable)) $inst->setCharset($charset);
            return $inst;
         }
         
      public static function newInstanceGeneric(): Response
         {
            return new Response();
         }
   }
