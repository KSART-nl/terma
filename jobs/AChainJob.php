<?php

// Job
class AChainJob extends Thread {

	public function __construct($term) {
		$this->term = $term;
	}

	public function run() {
		$start = microtime(true);

		//POSTagging
		//Flexion
		//Conjugate, Subject, Adjective
		//Classify

		$stop = microtime(true);
		echo "From A ".$stop - $start;
	}
}