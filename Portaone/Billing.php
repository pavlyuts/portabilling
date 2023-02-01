<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

/**
 * Billing API wrapper class
 */

namespace Portaone;

use WpOrg\Requests\Requests;
use Psr\Log;
use Portaone\Exceptions\{
    BillingAuthError,
    BillingConnectionError,
    BillingAPIError
};
use \Portaone\Storage\{
    SessionStorageInterface,
    SessionNoStorage
};

/**
 * Billing API class to use with password or token auth
 * 
 * Uses PSR-3 logger for logging
 * The session data may be retrived and stored outside with a class, 
 * impementing SessionStorageInterface
 * 
 */
Class Billing extends BillingBase {

    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const API_BASE = '/rest';
    const ESPF_BASE = '/espf/v1';
    const LOCAL_TIMEZONE = 'UTC';

    protected $host;
    protected $account;
    protected $options;
    protected $session = null;
    protected $storage;

    /**
     * Startst billing session, use session id or logins with credentials
     * 
     * @param array $config - configuration array, see config.sample.php for details
     * $param SessionStorageInterface $storage - object to store session data
     * @param LoggerInterface $logger - PSR-3 compatible instance for logging
     */
    function __construct(array $config, SessionStorageInterface $storage = null, Log\LoggerInterface $logger = null) {
        parent::__construct($logger);
        $this->host = $config['host'] ?? '';
        $this->account = $config['account'] ?? [];
        $this->options = $config['options'] ?? [];
        $this->storage = $storage ?? new SessionNoStorage();
        $this->session = $this->storage->restore();
        $this->checkToken();
    }

    /**
     * Convert billing-supplied UTC time string to DateTime object with target 
     * timezone
     * 
     * @param string $billingTime - datetime string as billing returns
     * @param string $timezone - timezone string like 'Europe/London" or '+3000',
     *                         as defined at https://www.php.net/manual/en/datetimezone.construct.php
     * @return \DateTime
     */
    static function timeToLocal(string $billingTime, string $timezone = null): \DateTime {
        return (new \DateTime($billingTime, new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone($timezone ?? static::LOCAL_TIMEZONE));
    }

    /**
     * Convert Datetime object to billing API string at UTC
     * 
     * @param \DateTime $time - Object to convert
     * @return string   Billing API-type datetime string, shifted to UTC
     */
    static function timeToBilling(\DateTime $time): string {
        return $time->setTimezone(new \DateTimeZone('UTC'))->format(static::DATETIME_FORMAT);
    }

    public function billingCall(string $endpoint, array $data = []) {
        $this->checkToken();
        $response = $this->httpCall($this->host . static::API_BASE . $endpoint,
                $data, ['Authorization' => 'Bearer ' . $this->session['access_token']]);
        if (false === ($filename = $this->extractFileName($response->headers))) {
            return $response->decode_body(true);
        }
        return [
            'filename' => $filename,
            'file' => $response->body,
        ];
    }

    public function getUsername() {
        if (!isset($this->session['access_token'])) {
            return null;
        }
        try {
            return json_decode(base64_decode(explode('.', $this->session['access_token'])[1] ?? ''), true)['login'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function httpCall(string $uri, array $data, array $headers = []) {
        try {
            $response = Requests::post($uri, array_merge(["Content-Type" => "application/json"], $headers),
                            json_encode(['params' => $data], JSON_UNESCAPED_UNICODE), $this->options);
        } catch (\WpOrg\Requests\Exception $e) {
            throw new BillingConnectionError($e->getMessage());
        }
        if ($response->success) {
            return $response;
        }
        if ($response->status_code != 500) {
            throw new BillingConnectionError('Http return code ' . $response->status_code, $response->status_code);
        } else {
            $err = $response->decode_body(true);
            if ($err['faultcode'] == 'Server.Session.auth_failed') {
                throw new BillingAuthError('Auth error, code:' . $err['faultcode'] . ' Message:' . $err['faultstring']);
            }
            throw new BillingAPIError($err['faultstring'], $err['faultcode']);
        }
    }

    protected function extractFileName(\WpOrg\Requests\Response\Headers $headers) {
        if (!(strpos($headers->offsetGet('content-type'), 'application/json') === false)) {
            return false;
        }
        if (1 === preg_match('/filename="([^"]+)"/', $headers->offsetGet('content-disposition'), $m)) {
            return $m[1];
        }
    }

    /**
     * Logins to the session with configured credentials, fill up session structure
     */
    protected function login() {
        if (!isset($this->account['login']) || (!isset($this->account['password']) && !isset($this->account['token']))) {
            $this->logDebug('Credentials required', array('account' => $this->account));
            throw new BillingAuthError('Credentials required');
        }
        $response = $this->httpCall($this->host . static::API_BASE . '/Session/login', $this->account);
        $this->session = $response->decode_body(true);
        $this->storage->save($this->session);
    }

    /**
     * Closes the session explicitly
     */
    public function logout() {
        if (!isset($this->session['access_token'])) {
            return;
        }
        try {
            $this->httpCall($this->host . static::API_BASE . '/Session/logout',
                    ['params' => [
                            'access_token' => $this->session['access_token'],
                        ]
                    ]
            );
        } catch (BillingAPIError $e) {
            
        }
        $this->storage->clean();
    }

    protected function checkToken() {
        if (!isset($this->session['refresh_token'])) {
            $this->login();
            return;
        }
        if ($this->tokenExpired()) {
            try {
                $response = $this->httpCall($this->host . static::API_BASE . '/Session/refresh_access_token',
                        ['params' => [
                                'refresh_token' => session['refresh_token'],
                            ]
                        ]
                );
            } catch (BillingAPIError $e) {
                $this->logDebug("Tocket refresh failed, relogin");
                $this->login();
                return;
            }
            $this->session = merge_array($this->session, $response->decode_body(true));
            $this->storage->save($this->session);
        }
    }

    protected function tokenExpired(): bool {
        $token = $this->timeToLocal($this->session['expires_at'], 'UTC');
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        return ($token->getTimestamp() - $now->getTimestamp()) < 10;
    }

}
