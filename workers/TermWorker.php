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

	public $postag_status = "Untagged";
	public $flexion_status = "Unflexioned";
	public $classify_status = "Unclassified";
	public $wiki_status = "Unwikied";
	public $association_status = "Unassociationed";
	public $kunstgehalt_status = "Unkunstgehalted";
	public $content_status = "Uncontented";
	public $defintion_status = "Undefinitioned";
	public $context_status = "Uncontexted";

	public $term;

    public function run() {
    	// echo 'Running '.$this->getStacked().' jobs'.PHP_EOL;
    }
}