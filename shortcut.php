<?php

require 'vendor/autoload.php';
require 'config/env.php';
require 'start.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$neo4j = ClientBuilder::create()
    ->addConnection('default', $neo4j_default_connection) // HTTP connection config (port is optional)
    ->addConnection('bolt', $neo4j_bolt_connection) // BOLT connection config (port is optional)
    ->build();

// Result contains a collection (array) of Record objects
$count_result = @$neo4j->run(
'MATCH (s:ns0_ScopeNote)<-[rel:ns1_scopeNote]-(r:Resource)
WHERE s.ns6_value IS NOT NULL AND r.ns2_label IS NOT NULL
RETURN
count(r) AS count
'
);

// Get count record
$terms_count = @$count_result->getRecord();
$terms_count = $terms_count->value('count'); //27643

$limitation = 10;
$required_loops = ceil($terms_count / $limitation);

for ($current_loop = 0; $current_loop <= $required_loops; $current_loop++) {

	// Select ten
	$cql = 'MATCH (s:ns0_ScopeNote)<-[rel:ns1_scopeNote]-(r:Resource)
WHERE s.ns6_value IS NOT NULL AND r.ns2_label IS NOT NULL
RETURN
r.ns2_label AS label,
r.ns1_prefLabel AS prefLabel,
r.ns1_altLabel AS altLabel,
r.ns0_parentString AS parentString,
s.ns6_value AS scopeNote
ORDER BY r.ns2_label
SKIP '.($current_loop*$limitation).'
LIMIT '.$limitation;
echo $cql."<br>";
	$term_result = @$neo4j->run($cql);
	$terms = @$term_result->records();

	foreach ($terms as $term_key => $term) {
		$resultTerm = new ResultTerm();
		$resultTerm->label = isset($term->value('label')) ? $term->value('label') : "";
		$resultTerm->prefLabel = isset($term->value('prefLabel')) ? $term->value('prefLabel') : "";
		$resultTerm->altLabel = isset($term->value('altLabel')) ? $term->value('altLabel') : "";
		$resultTerm->parentString = isset($term->value('parentString')) ? $term->value('parentString') : "";
		$resultTerm->scopeNote = isset($term->value('scopeNote')) ? $term->value('scopeNote') : "";
		$resultTerm->save();
	}

}

