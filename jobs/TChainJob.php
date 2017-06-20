<?php

// Job
class TChainJob extends Thread {

	public function __construct($term) {
		$this->term = $term;
	}

	public function run() {
		$start = microtime(true);

		//Content
		//Defintions
		//Context

		$stop = microtime(true);
		echo "From T ".$stop - $start.PHP_EOL;
	}
}