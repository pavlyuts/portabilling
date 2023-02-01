<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone\Storage;

/**
 * Class to use PHP Session storage
 *
 */
class SessionPHPStorage implements SessionStorageInterface {

    const SESSION_STORAGE_NAME = 'PortaoneBillingSession';

    protected $sessionName;

    public function __construct(string $sessionName = null) {
        $this->sessionName = $sessionName;
    }

    public function clean() {
        unset($_SESSION[static::SESSION_STORAGE_NAME]);
    }

    public function restore(): ?array {
        switch (session_status()) {
            case PHP_SESSION_ACTIVE:
                break;
            case PHP_SESSION_DISABLED:
                throw new BillingException("Session is disabled, fatal error");
            case PHP_SESSION_NONE:
                if (!is_null($this->sessionName)) {
                    session_name($this->sessionName);
                }
                if (!session_start()) {
                    throw new BillingException("Failed to start session");
                }
        }
        return $_SESSION[static::SESSION_STORAGE_NAME] ?? null;
    }

    public function save(array $session) {
        $_SESSION[static::SESSION_STORAGE_NAME] = $session;
    }

}
