<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone;

/**
 * BillingFile class extends class Billing with in file session storage
 * add 'file' key to your config array and the sesson will be stored and retrieved
 */
class BillingFile extends Billing {

    protected $sessionFile = null;

    /**
     * @param array $config - configuration array, see config.sample.php for details
     * @param LoggerInterface $logger - PSR-3 compatible instance for logging
     */
    function __construct(array $config, \Psr\Log\LoggerInterface $logger = null) {
        parent::__construct($config, $logger);
        if (!isset($this->config['file'])) {
            $this->logError("Parameter 'file' is not found in config array.");
        }
    }

    /**
     * Clears session storage by deleting the file
     */
    protected function clear() {
        if (isset($this->config['file'])) {
            if (!unlink($this->config['file'])) {
                $this->logError("Can't delete session file " . $this->config['file']);
            } else {
                $this->logDebug("Cleared session file " . $this->config['file']);
            }
        }
    }

    /**
     * Restores saved session id from file
     */
    protected function restore() {
        if (isset($this->config['file'])) {
            $sid = file_get_contents($this->config['file']);
            if (false === $sid) {
                $this->logInfo("Can't read session file " . $this->config['file'] . ".");
            } else {
                $this->sessionId = $sid;
                $this->logDebug("Session $sid restored from file" . $this->config['file']);
            }
        }
    }

    /**
     * Saves sesson id to desired file
     */
    protected function save() {
        if (isset($this->config['file']) && !is_null($this->sessionId)) {
            $r = file_put_contents($this->config['file'], $this->sessionId);
            if (false === $r) {
                $this->logError("Can't write session file " . $this->config['file']);
            } else {
                $this->logDebug("Session $this->sessionId saved to file " . $this->config['file']);
            }
        }
    }

}
