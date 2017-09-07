<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';
require 'config/env.php';
require 'start.php';

require 'functions.php';

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
$expressions 	= ["discipline","style","movement","proces","method","technique","material","result","company","function","exposure","subject"];
$expression_count = count($expressions);

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

		if( strpos(@$term->value('label'), "<") === false ) {
			$resultTerm = new ResultTerm();
			$resultTerm->label = @$term->value('label') !== null ? trim(preg_replace("/\([^)]+\)/","",$term->value('label'))) : "";
			$resultTerm->prefLabel = @$term->value('prefLabel') !== null ? $term->value('prefLabel') : "";
			$resultTerm->altLabel = @$term->value('altLabel') !== null ? $term->value('altLabel') : "";
			$resultTerm->parentString = @$term->value('parentString') !== null ? $term->value('parentString') : "";
			$resultTerm->scopeNote = @$term->value('scopeNote') !== null ? $term->value('scopeNote') : "";

			preg_match('/\((.*?)\)/', $term->value('label'), $contextMatch);
			$resultTerm->context = isset($contextMatch[0]) ? str_replace(["(",")"], "", $contextMatch[0]) : "";

			//Classify presumably
			$categories = classify_presumably($resultTerm->parentString, $expressions);
			//Classify primitively
			$primitives = classify_primitively($resultTerm->label, $expressions);
			//Classify all combined
			$resultTerm = classify_all($resultTerm, $categories, $primitives);
			$resultTerm->save();

			
		}

	}

}