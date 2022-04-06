<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body;


class Text extends body\Response implements body\ITranscodable
   {
      use body\TTranscodable;
      
      
      /**
       * Returns data as string in php's internal/default encoding
       * @return string
       */
      final public function getDataString(): string
         {
            return $this->transcodeToInternal(parent::getDataString());
         }
   }
