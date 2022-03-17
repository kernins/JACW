<?php
namespace lib\dp\Curl\async\dispatcher;
use lib\dp\Curl\async\DispatcherAbstract, lib\dp\Curl\session;


class Simple extends DispatcherAbstract implements \IteratorAggregate
   {
      protected array $transCompleted = [];
      
      
      
      protected function onTransferCompleted(session\HandlerAbstract $trans): void
         {
            $this->transCompleted[] = $trans;
         }
      
      
      public function getIterator(): \Generator
         {
            foreach($this->transCompleted as $trans)
               {
                  yield $trans->getResponse();
               }
         }
         
      public function purgeCompletedTransfers(): array
         {
            $transCompl = $this->transCompleted;
            $this->transCompleted = [];
            return $transCompl;
         }
   }
