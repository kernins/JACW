<?php
namespace lib\dp\Curl\session;
use lib\dp\Curl\IStream, lib\dp\Curl\exception;


class Config
   {
      protected const OPTS_DEFAULT = [
         //CURLOPT_RETURNTRANSFER  => true
      ];
      
      /*protected const OPTS_LOCKED = [
         
      ];*/
   
      
      private array $_opts = [];
      
      
      
      public function __construct(array $opts=[])
         {
            $this->setOpts($opts + static::OPTS_DEFAULT);
         }
      
      
      
      final public function merge(self $cfg): self
         {
            return $this->setOpts($cfg->toArray());
         }
         
      /**
       * Generic transparent bulk setter
       * No opt/value filtering/validation/normalization is performed
       * NULL values are allowed and have a special meaning for some opts,
       * e.g. CURLOPT_KRB4LEVEL
       * 
       * @param array $opts
       * @return self
       * @throws exception\UnexpectedValueException
       */
      final public function setOpts(array $opts): self
         {
            if(empty($opts)) throw new exception\UnexpectedValueException('No opts to set given');
            
            $this->_opts = $opts + $this->_opts;
            return $this;
         }
         
      /**
       * Generic transparent setter
       * No opt/value filtering/validation/normalization is performed
       * NULL values are allowed (and have special meaning for some opts, at least CURLOPT_KRB4LEVEL)
       * 
       * @param int     $opt
       * @param mixed   $value
       * @return self
       */
      final public function setOpt(int $opt, $value): self
         {
            $this->_opts[$opt] = $value;
            return $this;
         }
         
      /**
       * @param int $opt
       * @return self
       */
      final public function unsetOpt(int $opt): self
         {
            unset($this->_opts[$opt]);
            return $this;
         }
      
      
      
      public function returnTransfer(bool $val): self
         {
            return $this->setOpt(CURLOPT_RETURNTRANSFER, $val);
         }
         
      public function dlIntoFile(): self
         {
         
         }
         
      public function useProxy(): self
         {
         
         }
         
      public function conTimo(float $timo): self
         {
            $this->setOpt(CURLOPT_CONNECTTIMEOUT_MS, (int)($timo*1000));
            return $this;
         }
         
      public function sessTimo(float $timo): self
         {
            $this->setOpt(CURLOPT_TIMEOUT_MS, (int)($timo*1000));
            return $this;
         }
         
         
      public function toArray(): array
         {
            return $this->_opts;
         }
   }
