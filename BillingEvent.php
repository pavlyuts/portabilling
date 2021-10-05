<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone;

use Psr\Log;

/**
 * Class to provide basic workframe to handle events from PortaOne billing.
 * Must work within HTTP POSTprocessing context, as it takes posted values and 
 * process an event. Intended to answer POST requests, generated by 
 * FullEventSender.pm script (see docs).
 * 
 * To build event processing, extend the class with your own and define a method
 * for each event. List of available events and camelcased methid name could be 
 * found at Events.php. Method will accept stdClass, containing the data, sent 
 * by billing. The data structure differs for different event, please, 
 * refer PortaBilling docs.
 */
class BillingEvent extends BillingBase {

    protected $notImplementedError;
    protected $body;

    /**
     * Construct get the data from $_POST, check it and handle the event
     * 
     * @param $account - array of ('user' => username, 'password' => pass)
     *        If null, auth will be bypassed. Passed to checkCredentials intact, 
     *        so any different contentmay be used if you override checkCredentials.
     * @param \Portaone\Log\LoggerInterface $logger - logger object
     * @param bool $notImplementedError - if true, unimplemented event processing 
     *        will return 501 HTTP error to the billing system. Otherwise just 
     *        do nothing and return Ok.
     */
    function __construct($account = null, Log\LoggerInterface $logger = null,
            bool $notImplementedError = false) {
        parent::__construct($logger);
        $this->notImplementedError = $notImplementedError;
        $this->checkCredentials($account);
        $this->runEvent($this->parseBody());
    }

    /**
     * Do basic HTTP auth. If $account is null - bybass auth. 
     * Stops script oan returns HTTP 403 on credentials failure.
     * Override it if you want another auth type. If overriden, it MUST call error
     * method to terminate request processing on auth failure.
     * 
     * @param $account array of ('user' => username, 'password' => pass)
     *      
     */
    protected function checkCredentials($account)  {
        if (is_null($account)) { //Authentificate averything if $account is null
            $this->logDebug('Account is null, authentificate whatever.');
            return;
        }
        if (!(isset($account['user']) && isset($account['password']))) {
            $this->error(403, "Username and password required.", array ($account));
        }
        $r = $_SERVER;
        if (!(isset($r['PHP_AUTH_USER']) && isset($r['PHP_AUTH_PW']))) {
            $this->error(403, "Credentials not found.", $r);
        }
        if (!(($r['PHP_AUTH_USER'] == $account['user']) && ($r['PHP_AUTH_PW'] == $account['password']))) {
            $this->error(403, "Username or password does not match.", 
                    array( 'user' => $r['PHP_AUTH_USER'], 'pass' => $r['PHP_AUTH_PW'] ));
        }
    }

    /**
     * Retrieve HTTP request body and decode it to stdObject. Stop and return 
     * HTTP 404 on error.
     * 
     */
    protected function parseBody() {
        $this->body = file_get_contents('php://input');
        $data = json_decode($this->body);
        if (is_null($data)) {
            $this->error(404, 'Body JSON decode error', array('Request body' => $this->body));
        }
        if (!isset($data->event_type)) {
            $this->error(404, 'parameter event_type not found.', array ('Request body' => $this->body ));
        }
        if (!array_key_exists($data->event_type, Events::LIST)) {
            $this->error(404, 'Unknown event ' . $data->event_type . '.');
        }
        if (!isset($data->variables)) {
            $this->error(404, 'Variables not found', array('Request body' => $this->body));
        }
        return $data;
    }

    /**
     * Runs event handler according to event name and passes event data. Any 
     * exception will be catched and placed to debug log.
     * 
     * @param type $data - event variables
     */
    protected function runEvent($data) {
        $handler = Events::LIST[$data->event_type];
        $this->logInfo('Processing event ' . $data->event_type . '.', array($data->variables));
        try {
            $this->$handler($data->variables);
        } catch (Exception $e) {
            $this->error(500, "Exception while running event method", array('Exception' => $e->GetMessage()));
        }
        $this->logInfo('Processed event ' . $data->event_type . '.');
    }

    /**
     * error metod must be called to tell Billing system that event processing 
     * was not successful. It terminates the php script and sends desired HTTP 
     * status code
     * 
     * @param int $code - status code to sent. 200 may be used too, but not recommended.
     * @param string $message - message to log by local logger, tagged ERROR
     * @param array $data - extra data to log, tagged DEBUG
     */
    protected function error(int $code, string $message = null, array $data = null) {
        $this->logError("Return " . $code . ", " . $message, $data);
        http_response_code($code);
        exit;
    }

    /**
     * Catches all the calls to unimplemented methods. Generates HTTP status 500
     * if notImplementedError set to true. Otherwise log event to debug log and 
     * do nothing, return HTTP status 200 (success) to the billing.
     * 
     * @param type $name - method name
     * @param type $arguments - method arguments
     */
    function __call($name, $arguments) {
        if ($this->notImplementedError) {
            $this->error(404, 'Handler ' . $name . ' is not implemented', $arguments);
        }
        $this->logDebug('Catched not implemented handler ' . $name . '.', $arguments);
    }

}
