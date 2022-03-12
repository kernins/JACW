<?php
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'loader', 'loader', 'Loader.php']);

$loader=new \AF\system\core\loader\Loader(\AF\system\core\loader\Loader::MODE_NORMAL);
$loader->registerNamespacePath('lib\dp\Curl', __DIR__)->register();


//$req = new lib\dp\Curl\session\http\Request(new \lib\dp\Curl\session\http\URI('http://srvweb.com/redirwithcook.php'));
$req = new \lib\dp\Curl\session\http\request\POST(new \lib\dp\Curl\session\http\URI('http://srvweb.com/json.php'));
$req->postFormWwwUrlencoded('foo=bar&baz=kek');
$req->postMultipartFormData(['foo'=>'bar', 'baz'=>'kek']);
$req->postRawJSON(['foo'=>'bar', 'baz'=>'kek']);
$req->setAuth('Bearer', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzZXJ2ZXIiOiJzdGFnaW5nLWludGVncmF0aW9uLnBvc2lvcy5jb20iLCJzdWIiOiIyNjA1OSIsImF1ZCI6InVzZXJhZG1pbiIsImd0cCI6ImF1dGhvcml6YXRpb25fY29kZSIsImlzcyI6IkxpZ2h0c3BlZWQiLCJleHAiOjE2NDY3ODA1NDMsImlhdCI6MTY0Njc3Njk0MywiZGlkIjoiYXBpIiwiY2xpZW50X2lkIjoiZ2Y1bVBMNGFSWk9nSUxpRUFvUUE0Y2dhckM2ZVVKVzciLCJqdGkiOiI2ODY3Y2Y2Yy0wOTIxLTRmMmEtODBjZS04MGQ2OWZmZTM1MGYiLCJjaWQiOjE1MTg5fQ.Le5jrSDK3EhWVg0WBiIsKP-p5JAkhPLiLebDx2OsrgM');
//$req->setAuth('Basic', 'user', 'passwd');
//$req = new lib\dp\Curl\session\http\Request(new \lib\dp\Curl\session\http\URI('https://google.ru'));
$cfg = new \lib\dp\Curl\session\http\Config();
//$cfg->returnTransfer(false);

/*$locs=[
   'https://google.com',
   '/index.php',
   '/foo/index.html',
   'index.html?kek=draft',
   './inde:xx.html',
   '?foo=bar',
   '../bak.kek',
   '../..'
];
   
$uri = new lib\dp\Curl\session\http\URI('https://google.ru/foo/bar/../baz/kek.html?original=arg&boo=bee');
$resp = new lib\dp\Curl\session\http\headers\Response($uri, '1.1', 302);
var_dump((string)$uri);

foreach($locs as $l)
   {
      //var_dump($l, (string)(clone $uri)->changePath($l));
      var_dump($l, (string)$resp->set('Location', $l)->getRedirLocationURI());
   }

//var_dump($cfg->toArray());
die();*/

$file= fopen('/tmp/heap/user/1000/heap/curl.txt', 'w');
//$cfg->setOpt(CURLOPT_FILE, $file);

/*
 * my_cookie =
  "example.com"    //Hostname
  SEP "FALSE"      //Include subdomains
  SEP "/"          //Path
  SEP "FALSE"      //Secure
  SEP "0"          //Expiry in epoch time format. 0 == Session
  SEP "foo"        //Name
  SEP "bar";       //Value
 */

$cooks=[
   "www.google.ru\tTRUE\t/\tFALSE\t".(time()+3600)."\tsub\tbaz",
   "google.ru\tTRUE\t/\tFALSE\t".(time()+3600)."\tfoo\tbar"
];
//$cfg->setOpt(CURLOPT_COOKIELIST, "www.google.ru\tTRUE\t/\tFALSE\t".(time()+3600)."\tsub\tbaz");
//$cfg->setOpt(CURLOPT_COOKIELIST, ".google.ru\tTRUE\t/\tFALSE\t".(time()+3600)."\tfoo\tbar");
//$cfg->setOpt(CURLOPT_COOKIELIST, implode("\r\n", $cooks));
$cfg->setOpt(CURLOPT_COOKIELIST, '');

$cfg->setOpt(CURLINFO_HEADER_OUT, true);

$sess=new \lib\dp\Curl\session\http\Handler($req, $cfg);
$resp=$sess->init()->exec()->getResponse();
//$resp2=$sess->exec();
//$resp2=$sess->testChgUri('https://yandex.ru')->exec();

fclose($file);

//var_dump($resp, $resp->getHTTPCode());
//var_dump($resp->getCookies());
//var_dump($resp->getCookies());
var_dump($resp->getStatusCode(), $resp->getData());


/*new \lib\dp\Curl\session\http\URI('google.ru');
new \lib\dp\Curl\session\http\URI('http://google.ru');
new \lib\dp\Curl\session\http\URI('https://google.ru');
new \lib\dp\Curl\session\http\URI('google.ru/');
new \lib\dp\Curl\session\http\URI('google.ru:443/foo');
new \lib\dp\Curl\session\http\URI('google.ru/foo/bar/');
new \lib\dp\Curl\session\http\URI('google.ru/baz/kek?query=string&bash=booz#tag');
new \lib\dp\Curl\session\http\URI('https://google.ru:80/baz/kek/?query=string&bash=booz');
new \lib\dp\Curl\session\http\URI('google.ru/#hash-tag');
new \lib\dp\Curl\session\http\URI('google.ru/?query=string');
new \lib\dp\Curl\session\http\URI('https://google.ru#tag');
new \lib\dp\Curl\session\http\URI('https://google.ru:8080#tag');
new \lib\dp\Curl\session\http\URI('google.ru#tag');
new \lib\dp\Curl\session\http\URI('https://google.ru?query=string');
new \lib\dp\Curl\session\http\URI('google.ru:8080?query=string');
new \lib\dp\Curl\session\http\URI('https://google.ru?query=string#tag');
new \lib\dp\Curl\session\http\URI('google.ru?query=string#tag');
new \lib\dp\Curl\session\http\URI('a:8341');
new \lib\dp\Curl\session\http\URI('http://a');
new \lib\dp\Curl\session\http\URI('a/');*/
//var_dump(new \lib\dp\Curl\session\http\cookies\TargetSpec('invalid.'));


//$gUri = new \lib\dp\Curl\session\http\URI('https://google.ru:80/baz/kek/?query=string&bash=booz');

/*var_dump(
   $gUri,
   (string)new lib\dp\Curl\session\http\cookies\ScopeSpec('example.com', '/foo/bar'),
   (string)new lib\dp\Curl\session\http\cookies\ScopeSpec(' .dotexample ', '/bar', true, true),
   //new lib\dp\Curl\session\http\cookies\ScopeSpec('example.dot.')
);*/

/*$cook='NID = 511=pgdWXgK08It2D7AeF67IJE9psgKaaiNv72UYUZmLWw5IkLn99hIzh66pxGMifB5WrUW6C_eNclKb6lAZ2_9E0CVjDqzL07p4F1-JYPQjIbD2ZyUgnn6UZM0MxAkvc85WqtyNkpg59_V-aMMiq4kPauxVZ89NufZ93H3T7_Uapyw; expires=Fri, 02-Sep-2022 06:35:10 GMT; path=/; domain= .google.ru ; HttpOnly ';
$cook2='1P_JAR=2022-03-03-06; expires=Sat, 02-Apr-2022 06:35:10 GMT; Max-Age=0; path=/; domain=google.ru; Secure; HttpOnly';

$r = new lib\dp\Curl\session\http\cookies\Response();
$ds= lib\dp\Curl\session\http\cookies\ScopeSpec::createFromURI($gUri);
$r->setFromHeader($cook)->setFromHeader($cook2, $ds);

var_dump($r);
foreach($r as $c) var_dump($c->isExpired());*/

//$dt1 = new DateTime('-1 second');
//$dt2 = new DateTime();
//var_dump($r, $dt1<$dt2, $dt1==$dt2, $dt1>$dt2, $dt1, $dt2);
