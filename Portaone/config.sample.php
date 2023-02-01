<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

/**
 * API class config template. Uncomment and use fields you need
 */
$BillingConfig = [
    //API host only, no trailing /
    'host' => 'https://server.domain.net',
    
    // API credentiatls
    'account' => [
        //Username for API user, mandatory
        'login' => 'username',
        //Password for API user, paoo or token required
        'password' => 'password',
        //API token to use instead password, needs username
        'token' => 'token'
    ],
    
    // Options for API HTTP request, as described at $optionas help at
    // https://requests.ryanmccue.info/api-2.x/classes/WpOrg-Requests-Requests.html#method_request
    'options' => [
        
    ],
    
    //File to store/retrieve API session data, sed for file storage
    'file' => '/somwhere/somefile'
];
