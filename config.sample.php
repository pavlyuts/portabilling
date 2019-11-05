<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

/**
 * API class config template. Uncomment and use fields you need
 */
$BillingConfig = array(
    //API URI, including any subdirectory, ends with /
    'api' => 'https://server.domain.net/rest/',
    
    //Should SSL be verified? false is defaults, uncomment for verify
    //'verify' => trye,
        
    //Username for API user, mandatry if no valid session id supplied
    //'login'     => 'username',
    
    //Password for API user
    //'password'  => 'password',
    
    //API token to use instead password, needs username
    //'token' => 'token'
);
