<?php

use JonnyW\PhantomJs\Client;

$client = Client::getInstance();
$client->getEngine()->setPath('/usr/bin/phantomjs');

/** 
 * @see JonnyW\PhantomJs\Http\Request
 **/
$request = $client->getMessageFactory()->createRequest('http://jonnyw.me', 'GET');

/** 
 * @see JonnyW\PhantomJs\Http\Response 
 **/
$response = $client->getMessageFactory()->createResponse();

// Send the request
$client->send($request, $response);

if($response->getStatus() === 200) {

    // Dump the requested page content
    echo $response->getContent();
}