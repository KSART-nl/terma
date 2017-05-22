<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

//Eloquent requirements
require 'vendor/autoload.php';
require 'config/database.php';
require 'config/api_key.php';
require 'start.php';

//Pear library
require 'FSM.php';
require 'callbacks.php';

//Start LaMachine virtual env
shell_exec('. /lamachine/bin/activate');
$GLOBALS["pixabay"] = new \Pixabay\PixabayClient(['key' => $pixabay_api_key]);

$source_terms = SourceTerm::inRandomOrder()->whereNotNull("term")->take(10)->get();
foreach($source_terms as $source_term) {

	$stack = [
		"term"			=> $source_term->term,
		"context"		=> $source_term->qualifier
	];
	$fsm = new FSM('Init', $stack);

	$fsm->addTransition('OrthographyTransition', 'Init', 'Ortographyed', 'OrthographyCallback');
	$fsm->addTransition('UniquenessTransition', 'Ortographyed', 'Uniquenessed', 'UniquenessCallback');
	$fsm->addTransition('PostagTransition', 'Uniquenessed', 'Postagged', 'PostagCallback');
	$fsm->addTransition('ClassifyTransition', 'Postagged', 'Classified', 'ClassifyCallback');
	$fsm->addTransition('FlexionTransition', 'Classified', 'Flexioned', 'FlexionCallback');
	$fsm->addTransition('DefinitionTransition', 'Flexioned', 'Definitioned', 'DefinitionCallback');
	$fsm->addTransition('ContextTransition', 'Definitioned', 'Contexted', 'ContextCallback');
	$fsm->addTransition('KunstgehaltTransition', 'Contexted', 'Kunstgehalted', 'KunstgehaltCallback');
	$fsm->addTransition('ContentTransition', 'Kunstgehalted', 'Contented', 'ContentCallback');
	$fsm->addTransition('AssociationTransition', 'Contented', 'Associationed', 'AssociationCallback');

	$fsm->process('OrthographyTransition');
	$fsm->process('UniquenessTransition');
	$fsm->process('PostagTransition');
	$fsm->process('ClassifyTransition');
	$fsm->process('FlexionTransition');
	$fsm->process('DefinitionTransition');
	$fsm->process('ContextTransition');
	$fsm->process('KunstgehaltTransition');
	$fsm->process('ContentTransition');
	$fsm->process('AssociationTransition');

}

shell_exec('. /lamachine/bin/deactivate');

//https://docs.google.com/spreadsheets/d/1DL5KNYvM8cTg6k5PsUJMkpltG6AJ12PQIiTwim90eQw/edit#gid=0