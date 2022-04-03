<?php
namespace lib\dp\Curl\session\http\body;
use lib\dp\Curl\exception;


class RequestForm
   {
      protected array $data;
      
      
      
      public function __construct(array $data)
         {
            if(empty($data)) throw new exception\UnexpectedValueException('No form data given');
            $this->data = $data;
         }
      
      
      public function getFormData(): string|array
         {
            return $this->data;
         }
   }
