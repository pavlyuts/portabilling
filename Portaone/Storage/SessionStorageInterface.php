<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone\Storage;

/**
 * Interface for session storage
 *
 */
interface SessionStorageInterface {

    public function save(array $session);

    public function restore(): ?array;

    public function clean();
}
