<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone\Exceptions;

/**
 * handling of Portaone Billing API error
 *
 */
class BillingAPIError extends BillingException {

    protected $portaCode;

    public function __construct(string $message = "", string $portaCode = "") {
        $this->portaCode = $portaCode;
        parent::__construct($message);
        return $this;
    }

    public function getPoratCode() {
        return $this->portaCode;
    }

    public function __toString(): string {
        return __CLASS__ . ' Code:' . $this->portaCode . ', Message:'.$this->message 
                . ' in '.$this->file.':'.$this->line.PHP_EOL.$this->getTraceAsString();
    }

}
