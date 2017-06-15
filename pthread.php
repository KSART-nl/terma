<?php

//27643 terms NL

require_once 'vendor/autoload.php';
require_once 'config/env.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$neo4j = ClientBuilder::create()
    ->addConnection('default', $neo4j_default_connection) // HTTP connection config (port is optional)
    ->addConnection('bolt', $neo4j_bolt_connection) // BOLT connection config (port is optional)
    ->build();

// Result contains a collection (array) of Record objects
$result = $neo4j->run(
'MATCH (s:ns0_ScopeNote)<-[rel:ns1_scopeNote]-(r:Resource)
WHERE s.ns6_value IS NOT NULL AND r.ns2_label IS NOT NULL
RETURN
r.ns2_label AS label,
r.ns1_prefLabel AS prefLabel,
r.ns1_altLabel AS altLabel,
r.ns0_parentString AS parentString,

s.ns6_value AS scopeNote

LIMIT 50'
);
// Get all records
$records = $result->getRecords();
var_dump($records); exit();


// Jobs
class TermToLowerJob extends Thread {
    public function __construct($term) {
        $this->term = $term;
    }

    public function run() {
    	// echo microtime(true).PHP_EOL;
        echo strtolower($this->term).PHP_EOL;
    }
}

class TermToUpperJob extends Thread {
    public function __construct($term) {
        $this->term = $term;
    }

    public function run() {
    	// echo microtime(true).PHP_EOL;
        echo strtoupper($this->term).PHP_EOL;
    }
}

// Worker
class TermWorker extends Worker {
    public function run() {
    	// echo 'Running '.$this->getStacked().' jobs'.PHP_EOL;
    }
}

$start = microtime(true);
$termpool = new Pool(5, TermWorker::class);
$terms = ['Schilderen', 'Tekenen', 'Beeldhouwen', 'Breien', 'Knutselen'];
foreach($terms as $term) {
	$termpool->submit(new TermToLowerJob($term));
	$termpool->submit(new TermToUpperJob($term));
}
$termpool->shutdown();
echo microtime(true) - $start;