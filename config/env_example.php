<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// Source database connection
$neo4j_default_connection = 'http://neo4j:password@localhost:7474';
$neo4j_bolt_connection = 'bolt://neo4j:password@localhost:7687';

// Result database connection
$capsule = new Capsule();
$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => '127.0.0.1', // local or remote
    'database'  => 'database',
    'username'  => 'username',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => ''
), "result_terms");
$capsule->setAsGlobal();
$capsule->bootEloquent();

// API Keys
$wordnik_api_key = "";
$bing_api_key = "";
$morguefile_api_key = "";
$pixabay_api_key = "";

// Env paths
$lamachine_path = "/opt/LaMachine";