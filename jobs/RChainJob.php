<?php

// Job
class RChainJob extends Thread {

	public function __construct($term) {
		$this->term = $term;
	}

	public function run() {
		$start = microtime(true);

		//Wiki
		//Associations
		//Kunstgehalt

		$stop = microtime(true);
		echo "From R ".$stop - $start.PHP_EOL;
	}
}