<?php
namespace lib\dp\Curl\session\http;
use lib\dp\Curl\session, lib\dp\Curl\exception;


class Expectation implements session\IExpectation, session\IRequestHintInjector
   {
      protected ?string $respBodyType = null;
      protected ?array  $respStatusCodes = null;
      
      
      
      public function __construct(?string $respBodyType=null, ?array $respStatusCodes=null)
         {
            if(!empty($respBodyType))
               {
                  try
                     {
                        $rbtReflect = new \ReflectionClass($respBodyType);
                        if(!$rbtReflect->isSubclassOf(body\response\TypedAbstract::class))
                           throw new exception\DomainException('BodyType must be an FQN of one of '.body\response\TypedAbstract::class.' subclasses');
                        $this->respBodyType = $respBodyType;
                     }
                  catch(\Exception $ex)
                     {
                        throw new exception\InvalidArgumentException(
                           'Invalid response body type specified: '.$ex->getMessage(),
                           $ex->getCode(),
                           $ex
                        );
                     }
               }
            $this->respStatusCodes = $respStatusCodes;
         }
      
      
      public function injectRequestHint(session\IRequest $req): void
         {
            if(!empty($this->respBodyType) && ($req instanceof Request))
               $req->addHeaders((new headers\Request())->setAccept($this->respBodyType::getHandleableContentType()));
         }
      
      
      public function validate(session\InfoProvider $info, ?session\IResponse $resp): void
         {
            $respCode = $info->getInfoRespCode();
            
            if(!empty($this->respBodyType))
               {
                  if(empty($resp)) throw new exception\UnexpectedValueException('No response received', $respCode);
                  if(!$resp->hasBody()) throw new exception\UnexpectedValueException('Response has no body', $respCode);
                  if(!($resp->getBody() instanceof $this->respBodyType)) throw new exception\UnexpectedValueException(
                     'Unexpected response body type: '.get_class($resp->getBody()),
                     $respCode
                  );
               }
         
            if(!empty($this->respStatusCodes) && !in_array($respCode, $this->respStatusCodes))
               throw new exception\UnexpectedValueException('Unexpected response code: '.$respCode, $respCode);
         }
   }
