<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'vendor/autoload.php';
require 'config/env.php';
require 'start.php';
//Pear library
// require 'FSM.php';
// require 'callbacks.php';
require 'stopwords.php';
require 'selenium.php';

//Workers, jobs and actions
require 'workers/TermWorker.php';
require 'jobs/PostagJob.php';
require 'jobs/ClassifyJob.php';
require 'actions/OrthographyAction.php';
require 'actions/UniquenessAction.php';

//use JonnyW\PhantomJs\Client;
use Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use GraphAware\Neo4j\Client\ClientBuilder;

//Selenium server url
$host = 'http://localhost:4444/wd/hub';
$desired_capabilities = DesiredCapabilities::firefox();
//Run headless Firefox
$driver = RemoteWebDriver::create($host, $desired_capabilities);

//Activate LaMachine virtual env
shell_exec('. '.$lamachine_path.'/lamachine/bin/activate');
$GLOBALS["pixabay"] = new \Pixabay\PixabayClient(['key' => $pixabay_api_key]);
// $GLOBALS["phantomjs"] = Client::getInstance();
$GLOBALS["stopwords"] = new Stopwords();

$neo4j = ClientBuilder::create()
    ->addConnection('default', $neo4j_default_connection) // HTTP connection config (port is optional)
    ->addConnection('bolt', $neo4j_bolt_connection) // BOLT connection config (port is optional)
    ->build();

// Result contains a collection (array) of Record objects
$result = @$neo4j->run(
'MATCH (s:ns0_ScopeNote)<-[rel:ns1_scopeNote]-(r:Resource)
WHERE s.ns6_value IS NOT NULL AND r.ns2_label IS NOT NULL
RETURN
r.ns2_label AS label,
r.ns1_prefLabel AS prefLabel,
r.ns1_altLabel AS altLabel,
r.ns0_parentString AS parentString,
s.ns6_value AS scopeNote
LIMIT 5'
);
// Get all records
$terms = @$result->records();

$start = microtime(true);
$termpool = new Pool(5, TermWorker::class);
foreach($terms as $term) {

	$termLabel = $term->value('label');
	$termPrefLabel = $term->value('prefLabel');
	$termAltLabel = $term->value('altLabel');
	$termParentString = $term->value('parentString');
	$termScopeNote = $term->value('scopeNote');

	$termArray = [
		"label" => $termLabel,
		"prefLabel" => $termPrefLabel,
		"altLabel" => $termAltLabel,
		"parentString" => $termParentString,
		"scopeNote" => $termScopeNote
	];

	$termLabel = OrtographyAction($termLabel);
	if($termLabel != false && UniquenessAction($termLabel)) {

		$termpool->submit(new PostagJob(["termArray" => $termArray, "lamachinePath" => $lamachine_path]));
		$termpool->submit(new ClassifyJob($termArray));

	}
	
}
$termpool->shutdown();

//Deactivate LaMachine virtual env
//shell_exec('deactivate');

$stop = microtime(true);
echo "End Pool ".(String)($stop - $start).PHP_EOL;

exit();
















//Facebook\WebDriver\get_woordenlijst_nomina_table("koe", $driver);
//Facebook\WebDriver\get_woordenlijst_adjectiva_table("leuk", $driver);
//Facebook\WebDriver\get_woordenlijst_genera_table("agent", $driver);



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