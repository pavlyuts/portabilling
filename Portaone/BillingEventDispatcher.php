<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Portaone;

use Psr\Log;

/**
 * Dispatches Portabilling event to multiple destinations according to route map
 *
 */
class BillingEventDispatcher extends BillingEvent {

    protected $map = [];

    /**
     * Same as parent plus routing map. 
     * 
     * @param array $map - routing array for billing events. defined as:
     * $map = [
     *    [
     *        'events' => ['Customer/New', 'Connection/*'],
     *        'target' => 'http://localhost:8080/eventhandler'
     *    ],
     * ];
     * 
     * Wildcard '*' will be expanded to any content between '/'separators but
     * will NOT match miltiple levels. It be replaced with '[^/]+'regex pattern, 
     * so test it in any regex tester
     */
    function __construct(array $map, $account = null, \Psr\Log\LoggerInterface $logger = null) {
        $this->logger = (is_null($logger)) ? new Log\NullLogger() : $logger;
        $this->expandMap($map);
        parent::__construct($account, $this->logger, true);
    }

    protected function runEvent($data) {
        $this->logDebug("Got event", ['body' => $this->body]);
        $requests = $this->prepareRequest($data->event_type);
        $responses = \Requests::request_multiple($requests);
        $this->logDebug("Request done, response", $responses);
        $this->processResponse($requests, $responses);
    }

    protected function getRoutes($event) {
        $found = [];
        foreach ($this->map as $val) {
            if (in_array($event, $val['events'])) {
                $found[] = $val['target'];
            }
        }
        return $found;
    }

    protected function prepareRequest($event) {
        if ([] == ($routes = $this->getRoutes($event))) {
            $this->logInfo("No route found for $event, exiting");
            exit;
        }
        $requests = [];
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $_SERVER['HTTP_AUTHORIZATION'],
        ];
        foreach ($routes as $route) {
            $requests[] = [
                'url' => $route,
                'headers' => $headers,
                'data' => $this->body,
                'type' => 'POST'
            ];
            $this->logInfo("Event $event dispatched to $route");
        }
        $this->logDebug("Requests prepared", $requests);
        return $requests;
    }

    protected function processResponse($requests, $responses) {
        $status = 520;
        foreach ($responses as $i => $response) {
            $this->logInfo('Endpoint ' . $response->url . ' returned code ' . $response->status_code);
            $status = ($status < $response->status_code) ? $status : $response->status_code;
        }
        http_response_code($status);
    }

    protected function expandMap(array $map) {
        foreach ($map as $key => $val) {
            $expandedEvents = [];
            foreach ($val['events'] as $event) {
                $expandedEvents = array_merge($expandedEvents, (substr_count($event, '*') == 0) ? [$event] : $this->expandEvent($event));
            }
            $this->map[] = [
                'events' => array_unique($expandedEvents),
                'target' => $val['target']
            ];
        }
    }

    protected function expandEvent(string $event) {
        $pattern = '%' . str_replace('*', '[^/]+', $event) . '%';
        $found = [];
        foreach (array_keys(Events::LIST) as $val) {
            if (preg_match($pattern, $val)) {
                $found[] = $val;
            }
        }
        $this->logDebug('Route pattern expanded', ['pattern' => $event, 'expanded' => $found]);
        return $found;
    }

}
