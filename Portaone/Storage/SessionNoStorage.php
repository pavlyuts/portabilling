<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone\Storage;

/**
 * Dumb storage to use when no storage given
 *
 */
class SessionNoStorage implements SessionStorageInterface{
    public function clean() {
        
    }

    public function restore(): ?array {
        return null;
    }

    public function save(array $session) {
        
    }

}
