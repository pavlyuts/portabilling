<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

/**
 * Abstract base class with logger
 */

namespace Portaone;

use Psr\Log;

/**
 * Abstract class to implement logging for all another classes
 * 
 * Uses PSR-3 logger for logging
 * 
 */
abstract class BillingBase {

    protected $logger;

    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger - logger object to use. 
     *         If null, NullLogger will be used
     */
    function __construct(Log\LoggerInterface $logger = null) {
        $this->logger = (is_null($logger)) ? new Log\NullLogger() : $logger;
    }

    /**
     * Logs info
     * 
     * @param string $message - info message
     * @param array $dump - extra data array to put in the log with debug level
     */
    protected function logInfo(string $message, array $dump = null) {
        $this->logger->info($message);
        if (!is_null($dump)) {
            $this->logger->debug('', $dump);
        }
    }

    /**
     * Logs error
     * 
     * @param string $message - error message
     * @param array $dump - extra data array to put in the log with debug level
     */
    protected function logError(string $message, array $dump = null) {
        $this->logger->error($message);
        if (!is_null($dump)) {
            $this->logger->debug('', $dump);
        }
    }

    /**
     * Logs debug information
     * 
     * @param string $message - message to log
     * @param array $dump - extra data array to put in the log
     */
    protected function logDebug(string $message, array $dump = null) {
        $this->logger->debug($message, (is_null($dump)) ? array () : $dump );
    }

}
