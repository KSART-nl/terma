<?php

// Job
class ClassifyJob extends Thread {

	private $termArray;

	public function __construct($termArray) {
		$this->termArray = $termArray;
	}

	public function run() {
		$start = microtime(true);

		$expressions 	= ["discipline","style","movement","proces","method","technique","material","result","company","function","exposure","subject"];

		$termLabel = $this->termArray['label'];
		$termParentString = $this->termArray['parentString'];

		/*
			Classify categorically:
			Classification by Dutch AAT facet
		*/
		//Set default all expressions
		$categories = array_fill_keys($expressions, 0);
		$termSplittedString = explode(',', $termParentString);
		$termLastString = trim($termSplittedString[count($termSplittedString) - 1]);
		$termFacet = str_replace(" Facet", "", $termLastString);
 		//Do facet matching, see: https://www.getty.edu/research/tools/vocabularies/aat_in_depth.pdf
 		if($termFacet === "Associated Concepts") $categories["movement"] += 1;
 		if($termFacet === "Objects" || $termFacet === "Physical Attributes") {
			$categories["material"] += 1;
			$categories["result"] += 1;
		}
		if($termFacet === "Styles and Periods") {
			$categories["style"] += 1;
			$categories["movement"] += 1;
		}
		if($termFacet === "Agents") $categories["function"] += 1;
		if($termFacet === "Activities") {
			$categories["technique"] += 1;
			$categories["discipline"] += 1;
			$categories["method"] += 1;
			$categories["proces"] += 1;
		}		
		if($termFacet === "Materials") $categories["material"] += 1;
		
		/*
			Classify primitively:
			Classification by text pattern
		*/		
		//Set default all expressions
		$primitives = array_fill_keys($expressions, 0);
		//Do singular matching
		if(preg_match("~^(.*)ism(e)?$~", $termLabel)) $primitives["movement"] += 1;
		if(preg_match("~^(.*)istisch(e)?$~", $termLabel)) $primitives["style"] += 1;
		if(preg_match("~^(.*)ing$~", $termLabel)) $primitives["technique"] += 1;
		if(preg_match("~(.*)( )?kunst$~", $termLabel)) $primitives["discipline"] += 1;
		if(preg_match("~(.*)ure(n)?$~", $termLabel)) $primitives["technique"] += 1;
		if(preg_match("~(.*)druk$~", $termLabel)) $primitives["technique"] += 1;
		if(preg_match("~(.*)erij$~", $termLabel)) $primitives["company"] += 1;
		if(preg_match("~(.*)(f|g)ie$~", $termLabel)) $primitives["discipline"] += 1;
		if(preg_match("~(.*)(loog|logen)$~", $termLabel)) $primitives["function"] += 1;
		if(preg_match("~(.*)(er|ers)$~", $termLabel)) $primitives["function"] += 1;
		//Do combinational matching
		if(preg_match("~^(.*)en$~", $termLabel)) {
			$primitives["technique"] += 1;
			$primitives["material"] += 1;
		}
		if(preg_match("~(.*)(je|tje|pje|kje)$~", $termLabel)) {
			$primitives["result"] += 1;
			$primitives["function"] += 1;
			$primitives["material"] += 1;
		}

		/*
			Classify presumably:
			Classification by POS label
		*/
		//Set default all expressions
		$assumptions = array_fill_keys($expressions, 0);
		while(true) {
			//Postag labels are available
			if($this->worker->postag_status === "Tagged") {
				print_r($this->worker->postag_labels); 
				break;
			} else if($this->worker->postag_status === "Untaggable") {
				//Not possible, to classify this way
				break;
			}
			//$assumptions
		}

		//Set classifications with method results
		$classifications = (array)[
			"categories" => $categories,
			"primitives" => $primitives,
			"assumptions" => $assumptions
			];
		$this->worker->classifications = $classifications;
		print_r($classifications);

		$stop = microtime(true);
		echo "From ClassifyJob ".(String)($stop - $start).PHP_EOL;
	}
}