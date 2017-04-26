<?php
//var_dump(Term::all());

function OrthographyCallback($symbol, &$payload, $currentState, $nextState) {
	$payload["term"] = mb_strtolower($payload["term"]);
	echo "Orthography transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	$allowed_charset = "abcdefghijklmnopqrstuvwxyzàáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżžæœĳß'-0123456789 ";
	if(preg_match("/[".$allowed_charset."]/", $payload["term"])) {
		echo "Orthography is valid\n";
	} else {
		echo "Orthography is invalid\n";
	}
}
function UniquenessCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Uniqueness transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	$found_term = Term::where("term", $payload["term"])->get();
	if(!count($found_term)) {
		echo "Term is still unique\n";
	} else {
		echo "Term is not unique\n";
	}
}
function PostagCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Postag transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	$term_file_name = str_replace(" ", "_", $payload["term"]);
	$create_term_file = "echo '".$payload["term"]."' > frogs/".$term_file_name.".txt";
	echo $create_term_file."\n";
	$frog_term_file = "lm && frog -t frogs/".$term_file_name.".txt -X frogs/".$term_file_name.".xml && deactivate";
	echo $frog_term_file."\n";
	shell_exec($create_term_file);
	shell_exec($frog_term_file);
	if(file_exists("frogs/".$term_file_name.".xml")) {
		$term_folia = file_get_contents("frogs/".$term_file_name.".xml");
		echo $term_folia."\n";
	}
}
function ClassifyCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Classify transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function FlexionCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Flexion transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function DefinitionCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Definition transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function ContextCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Context transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function KunstgehaltCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Kunstgehalt transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function ContentCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Content transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function AssociationCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Association transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n\n";
}