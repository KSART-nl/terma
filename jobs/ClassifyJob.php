<?php

// Job
class ClassifyJob extends Thread {

	public function __construct($term) {
		$this->term = $term;
	}

	public function run() {
		$start = microtime(true);

		$termLabel = $term->value('label');
		$termParentString = $term->value('parentString');

		/*
			Classify categorically:
			Classification by Dutch AAT facet
		*/
		echo $termParentString;


		/*
			Classify primitively:
			Classification by text pattern
		*/
		$expressions 	= ["discipline","style","movement","proces","method","technique","material","result","company","function","exposure"];
		//Set default all Expressions
		$classifications = array_fill_keys($expressions, 0);
		//Do singular matching
		if(preg_match("~^(.*)ism(e)?$~", $payload["term"])) $classifications["movement"] += 1;
		if(preg_match("~^(.*)istisch(e)?$~", $payload["term"])) $classifications["style"] += 1;
		if(preg_match("~^(.*)ing$~", $payload["term"])) $classifications["technique"] += 1;
		if(preg_match("~(.*)( )?kunst$~", $payload["term"])) $classifications["discipline"] += 1;
		if(preg_match("~(.*)ure(n)?$~", $payload["term"])) $classifications["technique"] += 1;
		if(preg_match("~(.*)druk$~", $payload["term"])) $classifications["technique"] += 1;
		if(preg_match("~(.*)erij$~", $payload["term"])) $classifications["company"] += 1;
		if(preg_match("~(.*)(f|g)ie$~", $payload["term"])) $classifications["discipline"] += 1;
		if(preg_match("~(.*)(loog|logen)$~", $payload["term"])) $classifications["function"] += 1;
		if(preg_match("~(.*)(er|ers)$~", $payload["term"])) $classifications["function"] += 1;
		//Do combinational matching
		if(preg_match("~^(.*)en$~", $payload["term"])) {
			$classifications["technique"] += 1;
			$classifications["material"] += 1;
		}
		if(preg_match("~(.*)(je|tje|pje|kje)$~", $payload["term"])) {
			$classifications["result"] += 1;
			$classifications["function"] += 1;
			$classifications["material"] += 1;
		}

		/*
			Classify presumably:
			Classification by POS label
		*/
		while(true) {
			//Postag labels are available
			if($this->worker->$postag_status == "Tagged") {
				print_r($this->worker->$postag_labels); 
				break;
			} else if($this->worker->$postag_status == "Untaggable") {
				//Not possible, to classify this way
				break;
			}
		}

		$this->worker->classifications = $classifications;

		$stop = microtime(true);
		echo "From PostagJob ".(String)($stop - $start).PHP_EOL;
	}
}