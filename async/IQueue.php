<?php
namespace lib\dp\Curl\async;
use lib\dp\Curl\session;


interface IQueue extends \Countable
   {
      public function enqueue(session\HandlerAbstract $trans): self;
      public function dequeue(): session\HandlerAbstract;
      
      public function isEmpty(): bool;
   }
