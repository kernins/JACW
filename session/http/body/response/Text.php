<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body\Response as ResponseBase;


class Text extends ResponseBase
   {
      protected ?string $charset = null;
      
      
         
      final public function setCharset(string $charset): static
         {
            $this->charset = $charset;
            return $this;
         }
      
      
      /**
       * Returns data as string in utf8 encoding
       * @return string
       */
      final public function getDataString(): string
         {
            return !empty($this->charset) && (strcasecmp($this->charset, 'utf8')!==0)?
               mb_convert_encoding(parent::getDataString(), 'utf8', $this->charset) :
               parent::getDataString();
         }
   }
