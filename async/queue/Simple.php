<?php
namespace lib\dp\Curl\async\queue;
use lib\dp\Curl\session, lib\dp\Curl\async;


class Simple implements async\IQueue
   {
      protected \SplQueue $queue;
      
      
      
      public function __construct()
         {
            $this->queue = new \SplQueue();
         }
      
      
      public function enqueue(session\HandlerAbstract $trans): self
         {
            $this->queue->enqueue($trans);
            return $this;
         }
         
      public function dequeue(): session\HandlerAbstract
         {
            //will throw if queue is empty
            return $this->queue->dequeue();
         }
      
      
      public function count(): int
         {
            return $this->queue->count();
         }
         
      public function isEmpty(): bool
         {
            return count($this) == 0;
         }
   }
