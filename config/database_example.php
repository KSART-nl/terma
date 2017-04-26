<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule = new Capsule;
$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'database',
    'username'  => 'username',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => ''
), "source_terms");

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