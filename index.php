<?php

require 'FSM.php';

$stack = [
	"term" => "schilderen"
];
$fsm = new FSM('Init', $stack);

function OrthographyCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Orthography transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function UniquenessCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Uniqueness transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}
function PostagCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Postag transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
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
	echo "Association transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
}

$fsm->addTransition('Orthography', 'Init', 'Ortographyed', 'OrthographyCallback');
$fsm->addTransition('Uniqueness', 'Ortographyed', 'Uniquenessed', 'UniquenessCallback');
$fsm->addTransition('Postag', 'Uniquenessed', 'Postagged', 'PostagCallback');
$fsm->addTransition('Classify', 'Postagged', 'Classified', 'ClassifyCallback');
$fsm->addTransition('Flexion', 'Classified', 'Flexioned', 'FlexionCallback');
$fsm->addTransition('Definition', 'Flexioned', 'Definitioned', 'DefinitionCallback');
$fsm->addTransition('Context', 'Definitioned', 'Contexted', 'ContextCallback');
$fsm->addTransition('Kunstgehalt', 'Contexted', 'Kunstgehalted', 'KunstgehaltCallback');
$fsm->addTransition('Content', 'Kunstgehalted', 'Contented', 'ContentCallback');
$fsm->addTransition('Association', 'Contented', 'Associationed', 'AssociationCallback');

$fsm->process('Orthography');
$fsm->process('Uniqueness');
$fsm->process('Postag');
$fsm->process('Classify');
$fsm->process('Flexion');
$fsm->process('Definition');
$fsm->process('Context');
$fsm->process('Kunstgehalt');
$fsm->process('Content');
$fsm->process('Association');