<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';

use JonnyW\PhantomJs\Client;

$client = Client::getInstance();
//$client->getEngine()->setPath('/usr/bin/phantomjs');
$client->getEngine()->setPath('/opt/phantomjs');

var_dump($client);

/** 
 * @see JonnyW\PhantomJs\Http\Request
 **/
$request = $client->getMessageFactory()->createRequest('http://jeroensteen.nl', 'GET');
$request->setTimeout(10000);

/** 
 * @see JonnyW\PhantomJs\Http\Response 
 **/
$response = $client->getMessageFactory()->createResponse();

// Send the request
$client->send($request, $response);

var_dump($response);
var_dump($request);

if($response->getStatus() === 200) {

    // Dump the requested page content
    echo $response->getContent();
}