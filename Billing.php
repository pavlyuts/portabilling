<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

/**
 * Billing API wrapper class
 */

namespace Portaone;

use Requests;
use Psr\Log;

/**
 * Billing API class to use with password or token auth
 * 
 * Uses PSR-3 logger for logging
 * The session may be retrived and stored outside, for example in HTML coockie
 * Session id may be supplied to continue session, class will relogin if
 * the session id not accepted by API
 * 
 */
Class Billing extends BillingBase { 

    protected $config;
    protected $sessionId = null;
    public $status = true; 
    public $errorCode = null;
    public $errorMessage = null;
    public $response = null;

    /**
     * Startst billing session, use session id or logins with credentials
     * 
     * @param array $config - configuration array, see config.sample.php for details
     * @param LoggerInterface $logger - PSR-3 compatible instance for logging
     * @param string $sessionId - session id to continue.
     */
    function __construct(array $config, Log\LoggerInterface $logger = null, string $sessionId = null) {
        parent::__construct($logger);
        $config['verify'] = (isset($config['verify'])) ? $config['verify'] : false ;
        $this->config = $config;
        (is_null($sessionId)) ? $this->restore() : $this->sessionId = $sessionId;
        if (is_null($this->sessionId)) {
            $this->login();
        }
    }
     
    /**
     * Returns session id
     * 
     * @return string - session Id, if present or null if not.
     */
    public function getSessionId() {
        return ($this->status) ? $this->sessionId : null;
    }

    /**
     * Calls API endpoint with params, check API docs for details
     * API docs: https://www.portaone.com/docs/PortaBilling_API.html
     * 
     * @param string $endpoint in a form of 'Session/login'
     * @param array $data according to API reference for 'params'

     * * @return boolean - true if siccess
     */
    public function call(string $endpoint, array $data = null) {
        if (!$this->status) { return false; }
        if (!in_array($endpoint, Methods::LIST)) {
            $this->logError('API endpoint check error for "'.$endpoint.'"');
            return false;
        }
        $request = array( 'auth_info' => json_encode(array('session_id' => $this->sessionId)));
        if (!is_null($data)) {
            $request['params'] = json_encode($data);
        }
        if (!$this->makeCall($endpoint, $request)) {
            if ($this->errorCode != 'Server.Session.check_auth.auth_failed') {
                return false;
                }
            $this->logger->info('Session expired, relogin');
            if ($this->login()) {
                return $this->makeCall($endpoint, $request);
            }
        }
        return true;
    }

    /**
     * Logins to the session with configured credentials
     * 
     * @return boolean - true is success
     */
    protected function login() {
        if (!isset($this->config['login'])) {
            $this->logError('Login name required', array('config' => $this->config));
        }
        $account = array('login' => $this->config['login']);
        if (isset($this->config['password'])) {
            $account['password'] = $this->config['password'];
        } elseif (isset($this->config['token'])) {
            $account['token'] = $this->config['token'];
        }
        $request = array(
            'params' => json_encode($account)
        );
        $this->logDebug('Account:', array('request' => $request));
        if ($this->makeCall('Session/login', $request)) {
            $this->sessionId = $this->response->session_id;
            $this->save();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Closes the session explicitly
     */
    public function logout() {
        if (!$this->status) { 
            $this->logError("Can't logout, no session");
            return false; 
        }
        $params = array ( 'params' => json_encode(
                array( 'session_id' => $this->sessionId )));
        if (!$this->makeCall('Session/logout', $params)) {
            return false;
        }    
        if ($this->response->success == 0) { //API returns failure
            $this->logError("Logout request failure");
            return false;
        }
        $this->clear();
        return true;
    }

    /**
     * Perfoms API call with prepared request array
     * 
     * @param string $endpoint
     * @param array $request
     * 
     * @return boolean - true if success
     */
    protected function makeCall(string $endpoint, array $request = array()) {
        $this->logDebug('API call, endpoint:'.$endpoint, array('request' => $request));
        try {
            $response = Requests::post($this->config['api'] . $endpoint, array(), $request
                            , array('verify' => $this->config['verify']));
        } catch (\Requests_Exception $e) {
            $this->logError('HTTP error ' . $e->getMessage(), array('config' => $this->config));
            return $this->failure('HTTP Error: ' . $e->getMessage());
        }
        if ($response->success) {
            $answer = json_decode($response->body);
            if (is_null($answer)) {
                $this->logError('Can not decode answer to JSON', array('request' => $request, 'response' => $response));
                return $this->failure("Can not decode answer to JSON");
            }
            return $this->ok($answer);
        } elseif ($response->status_code == 500) {
            $answer = json_decode($response->body);
            if (is_null($answer)) {
                $this->logError('Can not decode answer with status code 500 to JSON', array('request' => $request, 'Response' => $response));
                return $this->failure("Can not decode answer with status code 500 to JSON");
            }
            if ($answer->faultcode != 'Server.Session.check_auth.auth_failed') {
                $this->logError('API error ' . $answer->faultcode . ', Message: ' . $answer->faultstring, array('request' => $request, 'response' => $response));
            }
            return $this->failure('Code:' . $answer->faultcode . ', Message: ' . $answer->faultstring, $answer->faultcode);
        }
        $this->logError('HTTP error ' . $response->status_code, array('request' => $request, 'response' => $response));
        return $this->failure('HTTP Error: ' . $response->status_code);
    }

    /**
     * Sets class status on success, store response in the property
     * 
     * @param StdClass $response
     * @return boolean true
     */
    protected function ok($response) {
        $this->response = $response;
        $this->status = true;
        $this->errorCode = null;
        $this->errorMessage = null;
        return true;
    }

    /**
     * Sets class status on failure
     * 
     * @param string $message - error message
     * @param string $code - error code
     * @return boolean false
     */
    protected function failure(string $message = null, string $code = null) {
        $this->response = null;
        $this->status = false;
        $this->errorCode = $code;
        $this->errorMessage = $message;
        return false;
    }

    /**
     * Saves session_id to external store
     * Do nothing in this class
     */
    protected function save() {
        
    }

    /**
     * Restores session_id from external store
     * Do nothing in this class
     */
    protected function restore() {
        
    }
    
    /**
     * Clears session_id in the external store
     * Do nothing in this class
     */
    protected function clear() {
        
    }

}
