<?php
namespace lib\dp\Curl\session;


interface IExpectation
   {
      /**
       * @param InfoProvider     $info
       * @param IResponse|null   $resp
       * @return void
       * @throws \RuntimeException  On failed expectation
       */
      public function validate(InfoProvider $info, ?IResponse $resp): void;
   }
