<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

//Pear library
require 'FSM.php';
require 'callbacks.php';

//Eloquent requirements
require 'vendor/autoload.php';
require 'config/database.php';
require 'start.php';

system('/lamachine/bin/activate');

$source_terms = SourceTerm::take(100)->get();
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

system('/lamachine/bin/deactivate');