<?php
namespace lib\dp\Curl\session\http\body;


trait TTranscodable
   {
      /**
       * Charset of body content:
       * - as-received for response body
       * - as-to-be-sent for request body
       * 
       * @var string|null
       */
      protected ?string $charset = null;
      
      
      
      final public function setCharset(string $charset): static
         {
            $this->charset = $charset;
            return $this;
         }
      
      
      final protected function transcodeFromInternal(string $text): string
         {
            return $this->isTranscodeRequired()?
               mb_convert_encoding($text, $this->charset, self::_getInternalCharset()) :
               $text;
         }
      
      final protected function transcodeToInternal(string $text): string
         {
            return $this->isTranscodeRequired()?
               mb_convert_encoding($text, self::_getInternalCharset(), $this->charset) :
               $text;
         }
      
      final protected function isTranscodeRequired(): bool
         {
            return !empty($this->charset) && (strcasecmp($this->charset, self::_getInternalCharset())!==0);
         }
      
      
      private static function _getInternalCharset(): string
         {
            static $charset = null;
            if($charset === null)
               {
                  $charset = ini_get('default_charset');
                  if(empty($charset)) $charset = 'utf-8';
               }
            return $charset;
         }
   }
