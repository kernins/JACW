<?php
namespace lib\dp\Curl\session;


interface IRequestHintInjector
   {
      public function injectRequestHint(IRequest $req): void;
   }
