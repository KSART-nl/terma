<?php

// Job
class ClassifyJob extends Thread {

	private $termArray;

	public function __construct($termArray) {
		$this->termArray = $termArray;
	}

	public function run() {
		$start = microtime(true);

		$termLabel = $this->termArray['label'];
		$termParentString = $this->termArray['parentString'];

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
		if(preg_match("~^(.*)ism(e)?$~", $termLabel)) $classifications["movement"] += 1;
		if(preg_match("~^(.*)istisch(e)?$~", $termLabel)) $classifications["style"] += 1;
		if(preg_match("~^(.*)ing$~", $termLabel)) $classifications["technique"] += 1;
		if(preg_match("~(.*)( )?kunst$~", $termLabel)) $classifications["discipline"] += 1;
		if(preg_match("~(.*)ure(n)?$~", $termLabel)) $classifications["technique"] += 1;
		if(preg_match("~(.*)druk$~", $termLabel)) $classifications["technique"] += 1;
		if(preg_match("~(.*)erij$~", $termLabel)) $classifications["company"] += 1;
		if(preg_match("~(.*)(f|g)ie$~", $termLabel)) $classifications["discipline"] += 1;
		if(preg_match("~(.*)(loog|logen)$~", $termLabel)) $classifications["function"] += 1;
		if(preg_match("~(.*)(er|ers)$~", $termLabel)) $classifications["function"] += 1;
		//Do combinational matching
		if(preg_match("~^(.*)en$~", $termLabel)) {
			$classifications["technique"] += 1;
			$classifications["material"] += 1;
		}
		if(preg_match("~(.*)(je|tje|pje|kje)$~", $termLabel)) {
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
			if($this->worker->postag_status === "Tagged") {
				print_r($this->worker->postag_labels); 
				break;
			} else if($this->worker->postag_status === "Untaggable") {
				//Not possible, to classify this way
				break;
			}
		}

		$this->worker->classifications = $classifications;

		$stop = microtime(true);
		echo "From PostagJob ".(String)($stop - $start).PHP_EOL;
	}
}