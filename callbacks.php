<?php

set_time_limit(0);
ini_set('memory_limit', '9000M');

//Include the Apriori class
require_once("apriori.php");

function OrthographyCallback($symbol, &$payload, $currentState, $nextState) {
	$payload["term"] = mb_strtolower($payload["term"]);
	echo "<h1>{$payload['term']}</h1>";
	echo "Orthography transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	/*
	Alfa characters: abcdefghijklmnopqrstuvwxyz
	Alfa diacritic characters: àáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżž
	Alfa ligature characters: æœĳß
	Puntuation characters: '- 
	*/
	$allowed_charset = "abcdefghijklmnopqrstuvwxyzàáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżžæœĳß'-0123456789 ";
	if(preg_match("/[".$allowed_charset."]/", $payload["term"])) {
		echo "Orthography is valid\n";
	} else {
		echo "Orthography is invalid\n";
	}
}
function UniquenessCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Uniqueness transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	$found_term = Term::where("term", $payload["term"])->get();
	if(!count($found_term)) {
		echo "Term is still unique\n";
	} else {
		echo "Term is not unique\n";
	}
}
function PostagCallback($symbol, &$payload, $currentState, $nextState) {
	$payload["term"] = escapeshellcmd($payload["term"]);
	echo "Postag transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	$term_file_name = str_replace(" ", "_", $payload["term"]);
	$create_term_file = "echo '".$payload["term"]."' > frogs/".$term_file_name.".txt";
	$frog_term_file = "/lamachine/bin/frog -t frogs/".$term_file_name.".txt -X frogs/".$term_file_name.".xml";
	//shell_exec($create_term_file);
	//shell_exec($frog_term_file);
	if(file_exists("frogs/".$term_file_name.".xml")) {
		$folia = file_get_contents("frogs/".$term_file_name.".xml");
		$sxe = new SimpleXMLElement($folia);
		$sxe->registerXPathNamespace('f', 'http://ilk.uvt.nl/folia');
		$words = $sxe->xpath('//f:w');
		$words = json_decode(json_encode($words),TRUE);
		foreach ($words as $word_key => $word) {
			echo "Postag: ".$word["pos"]["@attributes"]["head"]."\n";
			echo "Lemma: ".$word["lemma"]["@attributes"]["class"]."\n";
		}
	}
}
function ClassifyCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Classify transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
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
	print_r($classifications);
}
function FlexionCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Flexion transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";

	//Term is Verba; start conjugation, with DOM from URL/file
	$html = file_get_html("http://www.mijnwoordenboek.nl/ww.php?woord=".urlencode($payload["term"]));
	$found = $html->find('div.slider-wrap', 1)->find('h2', 0)->plaintext;
	//Verb is found
	if(strpos($found, "Helaas, het werkwoord of de werkwoordsvorm") === false) {
		$conjugations = [];
		
		//Find font tag for infinitive wrapper
		$infinitive_fonts = $html->find('font');
		foreach ($infinitive_fonts as $font_key => $infinitive_font) {
			//Verb is Dutch
			if(strpos($infinitive_font->plaintext, "NL: ") !== false) {
				//Find b tag in font tag for infinitive text
				$infinitive = $infinitive_font->find('b', 0)->plaintext;
				$conjugations[] = $infinitive;
				//Find all td's for conjugations
				foreach($html->find('td') as $element) {
					//Not the conjugation type, but the conjugation it self
					if(!$element->find('i.icon-question-sign')) {
						//Trim person related stuff, like Hebben/Zijn as hulpwerkwoord (Person attributions, order is important because of ambiguity)
						$verba_regex = '~(dat )?(ik|jij|hij|wij|jullie|zij)( )?(hebben|hebt|heb|heeft|hadden|had|zal|zult|zullen|zouden|zou|was|waren)?( )?(?<verba>.*)( hebben)?~';
						if (preg_match_all($verba_regex, $element->plaintext, $verba_matches)) {
							foreach($verba_matches["verba"] as $verba_match) {
								if(strpos($verba_match, "Vervoeg zoals") === false) {
									$conjugation = trim($verba_match); //Trimmed conjugation
									$conjugation = str_replace(" hebben", "", $conjugation);
									$conjugations[] = $conjugation;
								}
							}						
						}
					}
				}
			}
		}		

		sort($conjugations);
		print_r(array_unique($conjugations));
	}

	//Term is Nomina or Genera
	//$url = "http://woordenlijst.org/#/?q=".urlencode($payload["term"]); //giraf

	//Term is Adjectiva

	//Term is Perioda

}
function DefinitionCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Definition transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";

	//"https://glosbe.com/gapi_v0_1/";

	//TODO: Use Glose API if Definitions are available, issue made here: https://github.com/subzeta/glosbe/issues/2
	//TODO: Use Scope note from Dutch AAT
}
function ContextCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Context transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";

	//TODO: Use Definition
}
function KunstgehaltCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Kunstgehalt transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";

	//TODO: Use WikiPedia
}
function ContentCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Content transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n";
	//Get images from Pixabay
	/*$results = $GLOBALS["pixabay"]->get(['q' => $payload["term"]], true);
	foreach ($results["hits"] as $hit_key => $hit) {
		try {
			echo "<img src='".$hit["webformatURL"]."' />";
		} catch (Exception $e) {
		}
	}*/

	//TODO: Use MorgueFile
}
function AssociationCallback($symbol, &$payload, $currentState, $nextState) {
	echo "Association transition: {$symbol} {$payload["term"]} {$currentState} {$nextState}\n\n";
	
	$output = "";

	//Get pages links, for term
	$json_links = file_get_contents("https://nl.wikipedia.org/w/api.php?action=query&format=json&titles=".urlencode($payload["term"])."&generator=links");
	$links 	= json_decode($json_links, true);

	//Query gives page links, for term
	if( isset($links["query"]["pages"]) ) {
		//Loop all pages links
		foreach($links["query"]["pages"] as $page) {
			if(isset($page["pageid"])) {
				//Get the page data, with the current page ID
				$pageurl 	= "https://nl.wikipedia.org/w/api.php?action=parse&prop=text&pageid=".$page["pageid"]."&format=json";
				$pagedata 	= file_get_contents($pageurl);
				$pagedata 	= json_decode($pagedata, true);
				//Parsing is fine
				if( isset($pagedata["parse"]) ) {
					$pageparsed = $pagedata["parse"];
					//HTML text exists
					if( isset($pageparsed["text"]["*"]) ) {
						$pagehtml 		= $pageparsed["text"]["*"];
						$pagehtml 		= str_replace( '<', ' <', $pagehtml);
						$pagestripped = strip_tags($pagehtml);
						$pagestripped = str_replace( '  ', ' ', $pagestripped);
						$pagestripped = preg_replace("~(\[(.*)\])~", "", $pagestripped);
						$pagestripped = str_replace([") ", " ("], " ", $pagestripped);
						$pagestripped = str_replace([";", ":", ")", "(", " - "], "", $pagestripped);
						$output 		.= trim($pagestripped);
					}
				}
			}
		}
	}

	//var_dump($output);
	//file_put_contents("wikidata.txt", $output);
	//exit();
	echo "Association output: ".$output."\n";

	//TODO: Use Apriori, unrelevant words

	//Transactions from Text file (file) or Text string (text)
	$data = ["text" => $output];
	$apriori = new Apriori($data);
	$associations = $apriori::get_associations();

	print_r($associations);
	exit();

}