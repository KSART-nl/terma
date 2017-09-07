<?php

function classify_presumably($parent_string, $expressions) {
	$categories = array_fill_keys($expressions, 0);
	$termSplittedString = explode(',', $parent_string);
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
	if($termFacet === "Brand Names") {
		$categories["subject"] += 1;
		$categories["material"] += 1;
	}

	return $categories;
}

function classify_primitively($label, $expressions) {
	$primitives = array_fill_keys($expressions, 0);
	//Do singular matching
	if(preg_match("~^(.*)ism(e)?$~", $label)) $primitives["movement"] += 1;
	if(preg_match("~^(.*)istisch(e)?$~", $label)) $primitives["style"] += 1;
	if(preg_match("~^(.*)ing$~", $label)) $primitives["technique"] += 1;
	if(preg_match("~(.*)( )?kunst$~", $label)) $primitives["discipline"] += 1;
	if(preg_match("~(.*)ure(n)?$~", $label)) $primitives["technique"] += 1;
	if(preg_match("~(.*)druk$~", $label)) $primitives["technique"] += 1;
	if(preg_match("~(.*)erij$~", $label)) $primitives["company"] += 1;
	if(preg_match("~(.*)(f|g)ie$~", $label)) $primitives["discipline"] += 1;
	if(preg_match("~(.*)(loog|logen)$~", $label)) $primitives["function"] += 1;
	if(preg_match("~(.*)(er|ers)$~", $label)) $primitives["function"] += 1;
	//Do combinational matching
	if(preg_match("~^(.*)en$~", $label)) {
		$primitives["technique"] += 1;
		$primitives["material"] += 1;
	}
	if(preg_match("~(.*)(je|tje|pje|kje)$~", $label)) {
		$primitives["result"] += 1;
		$primitives["function"] += 1;
		$primitives["material"] += 1;
	}

	return $primitives;
}

function classify_all($resultTerm, $categories, $primitives) {
	$resultTerm->discipline_categorical_prob = $categories["discipline"] / $expression_count;
	$resultTerm->discipline_primitive_prob = $primitives["discipline"] / $expression_count;
	$resultTerm->discipline_combined_prob = ($categories["discipline"] + $primitives["discipline"]) / 2;
	$resultTerm->style_categorical_prob = $categories["style"] / $expression_count;
	$resultTerm->style_primitive_prob = $primitives["style"] / $expression_count;
	$resultTerm->style_combined_prob = ($categories["style"] + $primitives["style"]) / 2;
	$resultTerm->movement_categorical_prob = $categories["movement"] / $expression_count;
	$resultTerm->movement_primitive_prob = $primitives["movement"] / $expression_count;
	$resultTerm->movement_combined_prob = ($categories["movement"] + $primitives["movement"]) / 2;
	$resultTerm->proces_categorical_prob = $categories["proces"] / $expression_count;
	$resultTerm->proces_primitive_prob = $primitives["proces"] / $expression_count;
	$resultTerm->proces_combined_prob = ($categories["proces"] + $primitives["proces"]) / 2;
	$resultTerm->method_categorical_prob = $categories["method"] / $expression_count;
	$resultTerm->method_primitive_prob = $primitives["method"] / $expression_count;
	$resultTerm->method_combined_prob = ($categories["method"] + $primitives["method"]) / 2;
	$resultTerm->technique_categorical_prob = $categories["technique"] / $expression_count;
	$resultTerm->technique_primitive_prob = $primitives["technique"] / $expression_count;
	$resultTerm->technique_combined_prob = ($categories["technique"] + $primitives["technique"]) / 2;
	$resultTerm->material_categorical_prob = $categories["material"] / $expression_count;
	$resultTerm->material_primitive_prob = $primitives["material"] / $expression_count;
	$resultTerm->material_combined_prob = ($categories["material"] + $primitives["material"]) / 2;
	$resultTerm->result_categorical_prob = $categories["result"] / $expression_count;
	$resultTerm->result_primitive_prob = $primitives["result"] / $expression_count;
	$resultTerm->result_combined_prob = ($categories["result"] + $primitives["result"]) / 2;
	$resultTerm->company_categorical_prob = $categories["company"] / $expression_count;
	$resultTerm->company_primitive_prob = $primitives["company"] / $expression_count;
	$resultTerm->company_combined_prob = ($categories["company"] + $primitives["company"]) / 2;
	$resultTerm->function_categorical_prob = $categories["function"] / $expression_count;
	$resultTerm->function_primitive_prob = $primitives["function"] / $expression_count;
	$resultTerm->function_combined_prob = ($categories["function"] + $primitives["function"]) / 2;
	$resultTerm->exposure_categorical_prob = $categories["exposure"] / $expression_count;
	$resultTerm->exposure_primitive_prob = $primitives["exposure"] / $expression_count;
	$resultTerm->exposure_combined_prob = ($categories["exposure"] + $primitives["exposure"]) / 2;
	$resultTerm->subject_categorical_prob = $categories["subject"] / $expression_count;
	$resultTerm->subject_primitive_prob = $primitives["subject"] / $expression_count;
	$resultTerm->subject_combined_prob = ($categories["subject"] + $primitives["subject"]) / 2;

	return $resultTerm;
}