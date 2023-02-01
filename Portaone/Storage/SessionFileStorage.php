<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone\Storage;

/**
 * Use file as session data storage
 */
class SessionFileStorage implements SessionStorageInterface {

    protected $fileName;

    public function __construct(string $fileName) {
        $this->fileName = $fileName;
    }

    public function clean() {
        if (!unlink($this->file)) {
            throw new BillingException("Can't delete session file " . $this->file);
        }
    }

    public function restore(): ?array {
        if (file_exists($this->fileName) &&
                (false !== ($content = file_get_contents($this->fileName))) &&
                (null !== ($session = json_decode($content, true)))) {
            return $session;
        }
        return null;
    }

    public function save(array $session) {
        if (false === file_put_contents($this->fileName, json_encode($session, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT))) {
            throw new BillingException("Can't save session to file " . $this->file);
        }
    }

}
