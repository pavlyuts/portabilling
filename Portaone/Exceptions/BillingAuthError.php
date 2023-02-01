<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone\Exceptions;

/**
 * Exception to handle authentification errors
 *
 */
class BillingAuthError extends BillingException {

    public function __construct(string $message = "Billing API authentification error") {
        return parent::__construct($message, 500);
    }

}
