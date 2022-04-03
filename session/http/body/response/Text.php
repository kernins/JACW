<?php
namespace lib\dp\Curl\session\http\body\response;
use lib\dp\Curl\session\http\body\Response as ResponseBase;


class Text extends ResponseBase
   {
      protected const INTERNAL_CHARSET = 'utf8';
      
      
      /**
       * @var string|null  Charset of received body content
       */
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
            return !empty($this->charset) && (strcasecmp($this->charset, static::INTERNAL_CHARSET)!==0)?
               mb_convert_encoding(parent::getDataString(), static::INTERNAL_CHARSET, $this->charset) :
               parent::getDataString();
         }
   }
