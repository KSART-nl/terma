<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// Source database connection
$neo4j_default_connection = 'http://neo4j:@localhost:7474';
$neo4j_bolt_connection = 'bolt://neo4j:@localhost:7687';

// Result database connection
$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
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