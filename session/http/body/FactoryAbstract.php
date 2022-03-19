<?php
namespace lib\dp\Curl\session\http\body;
use lib\dp\Curl\exception;


abstract class FactoryAbstract
   {
      final protected static function getImplFQNForContentType(string $kind, string $ct): ?string
         {
            $ctParts = preg_split('/[\/\-\+\.]/', $ct, -1, PREG_SPLIT_NO_EMPTY);
            if(count($ctParts) < 2) throw new exception\UnexpectedValueException('Invalid content-type given: '.$ct);
            
            $ctClass = array_shift($ctParts);
            $fqnClass = __NAMESPACE__.'\\'.$kind.'\\'.ucfirst($ctClass);

            $variants = [
               $fqnClass.implode('', array_map('ucfirst', $ctParts)), //FIXME: php 8.1 replace 'ucfirst' to ucfirst(...)
               $fqnClass //e.g. ...\Text for text/html
            ];
            
            foreach($variants as $v) {if(class_exists($v)) return $v;}
            return null;
         }
   }
