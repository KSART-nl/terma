<?php

require 'vendor/autoload.php';
require 'config/env.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$neo4j = ClientBuilder::create()
    ->addConnection('default', $neo4j_default_connection) // HTTP connection config (port is optional)
    ->addConnection('bolt', $neo4j_bolt_connection) // BOLT connection config (port is optional)
    ->build();

// Result contains a collection (array) of Record objects
$result = @$neo4j->run(
'MATCH (s:ns0_ScopeNote)<-[rel:ns1_scopeNote]-(r:Resource)
WHERE s.ns6_value IS NOT NULL AND r.ns2_label IS NOT NULL
RETURN
count(r) AS count
'
);

// r.ns2_label AS label,
// r.ns1_prefLabel AS prefLabel,
// r.ns1_altLabel AS altLabel,
// r.ns0_parentString AS parentString,
// s.ns6_value AS scopeNote
// LIMIT 5

// Get all records
$terms_count = @$result->records();
var_dump($terms_count->value('count'));