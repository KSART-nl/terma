<?php

// Worker
class TermWorker extends Worker {

	public $postag_labels = [];
	public $flexions = [];
	public $classifications = [];
	public $wikipage_urls = [];
	public $associations = [];
	public $kunstgehalts = [];
	public $contents = [];
	public $definitions = [];
	public $contexts = [];

	public $term;

    public function run() {
    	// echo 'Running '.$this->getStacked().' jobs'.PHP_EOL;
    }
}