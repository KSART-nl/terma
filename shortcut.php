<?php

require 'vendor/autoload.php';
require 'config/env.php';
require 'start.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$neo4j = ClientBuilder::create()
    ->addConnection('default', $neo4j_default_connection) // HTTP connection config (port is optional)
    ->addConnection('bolt', $neo4j_bolt_connection) // BOLT connection config (port is optional)
    ->build();

$limitation = 10;
$required_loops = ceil($terms_count / $limitation);
$expressions 	= ["discipline","style","movement","proces","method","technique","material","result","company","function","exposure","subject"];
$expression_count = count($expressions);

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
		$resultTerm->label = @$term->value('label') !== null ? $term->value('label') : "";
		$resultTerm->prefLabel = @$term->value('prefLabel') !== null ? $term->value('prefLabel') : "";
		$resultTerm->altLabel = @$term->value('altLabel') !== null ? $term->value('altLabel') : "";
		$resultTerm->parentString = @$term->value('parentString') !== null ? $term->value('parentString') : "";
		$resultTerm->scopeNote = @$term->value('scopeNote') !== null ? $term->value('scopeNote') : "";

		//Classify presumably
		$categories = array_fill_keys($expressions, 0);
		$termSplittedString = explode(',', $termParentString);
		$termLastString = trim($termSplittedString[count($termSplittedString) - 1]);
		$termFacet = str_replace(" Facet", "", $termLastString);
 		//Do facet matching, see: https://www.getty.edu/research/tools/vocabularies/aat_in_depth.pdf
 		if($termFacet === "Associated Concepts") $categories["movement"] += 1;
 		if($termFacet === "Objects" || $termFacet === "Physical Attributes") {
			$categories["material"] += 1;
			$categories["result"] += 1;
		}
		if($termFacet === "Styles and Periods") {
			$categories["style"] += 1;
			$categories["movement"] += 1;
		}
		if($termFacet === "Agents") $categories["function"] += 1;
		if($termFacet === "Activities") {
			$categories["technique"] += 1;
			$categories["discipline"] += 1;
			$categories["method"] += 1;
			$categories["proces"] += 1;
		}		
		if($termFacet === "Materials") $categories["material"] += 1;

		//Classify primitively
		$primitives = array_fill_keys($expressions, 0);
		//Do singular matching
		if(preg_match("~^(.*)ism(e)?$~", $termLabel)) $primitives["movement"] += 1;
		if(preg_match("~^(.*)istisch(e)?$~", $termLabel)) $primitives["style"] += 1;
		if(preg_match("~^(.*)ing$~", $termLabel)) $primitives["technique"] += 1;
		if(preg_match("~(.*)( )?kunst$~", $termLabel)) $primitives["discipline"] += 1;
		if(preg_match("~(.*)ure(n)?$~", $termLabel)) $primitives["technique"] += 1;
		if(preg_match("~(.*)druk$~", $termLabel)) $primitives["technique"] += 1;
		if(preg_match("~(.*)erij$~", $termLabel)) $primitives["company"] += 1;
		if(preg_match("~(.*)(f|g)ie$~", $termLabel)) $primitives["discipline"] += 1;
		if(preg_match("~(.*)(loog|logen)$~", $termLabel)) $primitives["function"] += 1;
		if(preg_match("~(.*)(er|ers)$~", $termLabel)) $primitives["function"] += 1;
		//Do combinational matching
		if(preg_match("~^(.*)en$~", $termLabel)) {
			$primitives["technique"] += 1;
			$primitives["material"] += 1;
		}
		if(preg_match("~(.*)(je|tje|pje|kje)$~", $termLabel)) {
			$primitives["result"] += 1;
			$primitives["function"] += 1;
			$primitives["material"] += 1;
		}

		preg_match('/\((.*?)\)/', $termLabel, $contextMatch);
		$resultTerm->context = isset($contextMatch[0]) ? $contextMatch[0] : "";

		$resultTerm->discipline_categorical_prob = $categories["discipline"] / $expression_count;
		$resultTerm->discipline_primitive_prob = $primitives["discipline"] / $expression_count;
		$resultTerm->discipline_combined_prob = ($categories["discipline"] + $primitives[""]) / 2;
		$resultTerm->style_categorical_prob = $categories["style"] / $expression_count;
		$resultTerm->style_primitive_prob = $primitives["style"] / $expression_count;
		$resultTerm->style_combined_prob = ($categories["style"] + $primitives[""]) / 2;
		$resultTerm->movement_categorical_prob = $categories["movement"] / $expression_count;
		$resultTerm->movement_primitive_prob = $primitives["movement"] / $expression_count;
		$resultTerm->movement_combined_prob = ($categories["movement"] + $primitives[""]) / 2;
		$resultTerm->proces_categorical_prob = $categories["proces"] / $expression_count;
		$resultTerm->proces_primitive_prob = $primitives["proces"] / $expression_count;
		$resultTerm->proces_combined_prob = ($categories["proces"] + $primitives[""]) / 2;
		$resultTerm->method_categorical_prob = $categories["method"] / $expression_count;
		$resultTerm->method_primitive_prob = $primitives["method"] / $expression_count;
		$resultTerm->method_combined_prob = ($categories["method"] + $primitives[""]) / 2;
		$resultTerm->technique_categorical_prob = $categories["technique"] / $expression_count;
		$resultTerm->technique_primitive_prob = $primitives["technique"] / $expression_count;
		$resultTerm->technique_combined_prob = ($categories["technique"] + $primitives[""]) / 2;
		$resultTerm->material_categorical_prob = $categories["material"] / $expression_count;
		$resultTerm->material_primitive_prob = $primitives["material"] / $expression_count;
		$resultTerm->material_combined_prob = ($categories["material"] + $primitives[""]) / 2;
		$resultTerm->result_categorical_prob = $categories["result"] / $expression_count;
		$resultTerm->result_primitive_prob = $primitives["result"] / $expression_count;
		$resultTerm->result_combined_prob = ($categories["result"] + $primitives[""]) / 2;
		$resultTerm->company_categorical_prob = $categories["company"] / $expression_count;
		$resultTerm->company_primitive_prob = $primitives["company"] / $expression_count;
		$resultTerm->company_combined_prob = ($categories["company"] + $primitives[""]) / 2;
		$resultTerm->function_categorical_prob = $categories["function"] / $expression_count;
		$resultTerm->function_primitive_prob = $primitives["function"] / $expression_count;
		$resultTerm->function_combined_prob = ($categories["function"] + $primitives[""]) / 2;
		$resultTerm->exposure_categorical_prob = $categories["exposure"] / $expression_count;
		$resultTerm->exposure_primitive_prob = $primitives["exposure"] / $expression_count;
		$resultTerm->exposure_combined_prob = ($categories["exposure"] + $primitives[""]) / 2;
		$resultTerm->subject_categorical_prob = $categories["subject"] / $expression_count;
		$resultTerm->subject_primitive_prob = $primitives["subject"] / $expression_count;
		$resultTerm->subject_combined_prob = ($categories["subject"] + $primitives[""]) / 2;

		$resultTerm->save();



	}



}

