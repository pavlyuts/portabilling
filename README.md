# PortaOne Billing API wrapper for PHP

## Purpose

PHP class to simplify communication to PortaOne billing system while creating
user portal applications or integration code. Build for composer with PSR-4 autoload, uses PSR-3 object for logging.

Please, refer to [PortaBilling API docs](https://www.portaone.com/docs/PortaBilling_API.html)

*Not properly tested, use at your own risk!*

## Class Billing

Class Billling uses config array to connect API and get session id or can use a supplied session id. If a sesson id provided, it will not try to login at init, but will try relogin if the session id failed.

It use [PSR-3 compatible logging interface](https://www.php-fig.org/psr/psr-3/), please, provide logging object of your choice and define severity filter on it. No logging if it omitted or null. Designed not to trow any exception, but no guarantee.

Constructor accepts:
- **config** mandatory array of configuration and account parameters, see  [the config sample](https://github.com/pavlyuts/portabilling/blob/master/config.sample.php).
- **log object**, optional PSR-3 log object. null by default, no logging.
- **session id**, optional session id to use.

Properties:
- **status** shows the last operation status, true for Ok.
- **errorCode** and **errorMessage** stores the last error code and error message. null for Ok.
- **response** holds the API answer, JSON-decoded to PHP stdObject.

Methods:
- **call** method calls API endpoind with supplied data array in accordance with "params" part as described in API docs. Endpoints are checked agains endpoint list and ERROR logs if not found. It will encode to JSON, so need just PHP array (not object!).
- **logout** explicitly closes the session.
- **getSessionId** returns active sesson id or null if sonmething wrong. You may want to put the session id to a user's coockie.

## Classes BillingFile and BillingMemcached
*Not yet implemented.*

Extends class Billing to store session id in the file or Memcached respectively. Useful for integration application by keeping application-wide session. Needs extra config options. If session id supplied on creation, it will be stored too.


## Installation

Not yet in Composer repo. Please, use it from github.

    repositories": [
        {
            "type":"package",
            "package": {
              "name": "pavlyuts/portabilling",
              "version":"master",
              "source": {
                  "url": "https://github.com/pavlyuts/portabilling.git",
                  "type": "git",
                  "reference":"master"
                }
            }
        }
    ],
    "require": {
        "pavlyuts/portabilling": "master"
    }



## Usage
Mean the library installed by Composer as described above with Monolog required too.

```
<?php

require '../vendor/autoload.php';

use Portaone\Billing;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//create configuration
$params = array(
    'api' => 'https://your-server.domain/rest/', //mind the /rest/ on the end
    'login' => 'yourlogin',
    'password' => 'yourpass',
); 

//create logger to stdout with ERROR level, try DEBUG also
$log = new Logger('BillingAPI'); //Tag for log
$log->pushHandler(new StreamHandler('php://stdout', Logger::ERROR));

//create Billing instance
$a = new Billing($params, $log);

//If problem, exit with error message
if (!$a->status) { 
    exit('Init failure: '.$a->errorMessage);
}

//Retrive and print the session id
$s = $a->getSessionId();
echo PHP_EOL.'Session id is:'.$s.PHP_EOL.PHP_EOL;

//create an instance for a known session id
$b = new Billing($params, $log, $s);

//If problem, exit with error message
if (!$b->status) { 
    exit('Init failure: '.$b->errorMessage);
}

//call API fuction without any mandatory fields
if (!$b->call('Currency/get_currency_list')) {
    // if fail, exit with message
    exit('Currency request failure:'.$b->errorCode); 
}
var_dump($b->response);

//call API with params, get session user id
if (!$b->call('Session/ping', array( 'session_id' => $b->getSessionId()))) {
    // if fail, exit with message
    exit('Ping failure: '.$b->errorMessage); 
}
var_dump($b->response);

//call API with params, get user info
if (!$b->call('User/get_user_info', array( 'i_user' => $b->response->user_id))) {
    // if fail, exit with message
    exit('User info filure: '.$b->errorMessage);
}
var_dump($b->response);
```







