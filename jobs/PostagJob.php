<?php

// Job
class PostagJob extends Thread {

	private $termArray;
	private $lamachinePath;

	public function __construct($payload) {
		$this->termArray = $payload["termArray"];
		$this->lamachinePath = $payload["lamachinePath"];
	}

	public function run() {
		$start = microtime(true);

		$termLabel = $this->termArray['label'];
		$termFilename = str_replace(" ", "_", $termLabel);

		file_put_contents("frogs/".$termFilename.".txt", $termLabel);
		$frog_term_file = $this->lamachinePath."/lamachine/bin/frog -t frogs/".$termFilename.".txt -X frogs/".$termFilename.".xml";
		shell_exec($frog_term_file);

		if(file_exists("frogs/".$termFilename.".xml")) {
			$folia = file_get_contents("frogs/".$termFilename.".xml");
			$sxe = new SimpleXMLElement($folia);
			$sxe->registerXPathNamespace('f', 'http://ilk.uvt.nl/folia');
			$words = $sxe->xpath('//f:w');
			$words = json_decode(json_encode($words),TRUE);
			foreach ($words as $word_key => $word) {
				$this->worker->postag_labels = array_merge(
					$this->worker->postag_labels,
					//Prevent Volatile object, cast to array
					(array)[
						"word" => $word_key,
						"label" => $word["pos"]["@attributes"]["head"],
						"lemma" => $word["pos"]["@attributes"]["class"]
					]
				);
			}
			if(count($words)) {
				$this->worker->postag_status = "Tagged";
			} else {
				$this->worker->postag_status = "Untaggable";
			}
		} else {
			$this->worker->postag_status = "Untaggable";
		}		

		$stop = microtime(true);
		echo "From PostagJob ".(String)($stop - $start).PHP_EOL;
	}
}