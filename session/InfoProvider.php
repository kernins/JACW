<?php
namespace lib\dp\Curl\session;


final class InfoProvider
   {
      private \CurlHandle $_hndl;
      
      
      
      public function __construct(\CurlHandle $curlHandle)
         {
            $this->_hndl = $curlHandle;
         }
      
      
      public function getInfo(int $opt)
         {
            return curl_getinfo($this->_hndl, $opt);
         }
      
      public function getInfoRespCode(): int
         {
            return $this->getInfo(\CURLINFO_RESPONSE_CODE);
         }
         
      /**
       * Will track redirects when FOLLOWLOCATION enabled
       * @return string    URL of current request
       */
      public function getInfoEffectiveURL(): string
         {
            return $this->getInfo(\CURLINFO_EFFECTIVE_URL);
         }
      
      
      public function getLastCurlCode(): int
         {
            return curl_errno($this->_hndl);
         }
      
      public function hasPendingError(): bool
         {
            return ($this->getLastCurlCode() != \CURLE_OK);
         }
      
      public function getLastErrorCode(): ?int
         {
            return $this->hasPendingError()? $this->getLastCurlCode() : null;
         }
      
      public function getLastErrorMessage(): ?string
         {
            return $this->hasPendingError()? curl_error($this->_hndl) : null;
         }
   }
