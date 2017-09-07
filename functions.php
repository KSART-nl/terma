<?php

function postag($term) {
	$tags = [];

	$term_file_name = str_replace(" ", "_", $term);
	$create_term_file = "echo '".$term."' > frogs/".$term_file_name.".txt";
	$frog_term_file = "/lamachine/bin/frog -t frogs/".$term_file_name.".txt -X frogs/".$term_file_name.".xml";
	shell_exec($create_term_file);
	shell_exec($frog_term_file);
	if(file_exists("frogs/".$term_file_name.".xml")) {
		$folia = file_get_contents("frogs/".$term_file_name.".xml");
		$sxe = new SimpleXMLElement($folia);
		$sxe->registerXPathNamespace('f', 'http://ilk.uvt.nl/folia');
		$words = $sxe->xpath('//f:w');
		$words = json_decode(json_encode($words),TRUE);
		foreach ($words as $word_key => $word) {

			$tags[$word_key]["label"] = $word["pos"]["@attributes"]["head"];
			$tags[$word_key]["label_full"] = $word["pos"]["@attributes"]["class"];
			$tags[$word_key]["lemma"] = $word["lemma"]["@attributes"]["class"];
			$tags[$word_key]["prob"] = $word["pos"]["@attributes"]["confidence"];			

		}
	}
	return $tags;
}

function classify_categorically($parent_string, $expressions) {
	$categories = array_fill_keys($expressions, 0);
	$termSplittedString = explode(',', $parent_string);
	$termLastString = trim($termSplittedString[count($termSplittedString) - 1]);
	$termFacet = str_replace(" Facet", "", $termLastString);

	//Do facet matching, see: getty.edu/research/tools/vocabularies/aat_in_depth.pdf
	if($termFacet === "Associated Concepts") $categories["movement"] += 1; // 1 / 4.75
	if($termFacet === "Objects" || $termFacet === "Physical Attributes") {
		$categories["material"] += 1; // 0.5 / 4.75
		$categories["result"] += 1; // 0.5 / 4.75
	}
	if($termFacet === "Styles and Periods") {
		$categories["style"] += 1; // 0.5 / 4.75
		$categories["movement"] += 1; // 0.5 / 4.75
	}
	if($termFacet === "Agents") $categories["function"] += 1;  // 1 / 4.75
	if($termFacet === "Activities") {
		$categories["technique"] += 1; // 0.25 / 4.75
		$categories["discipline"] += 1; // 0.25 / 4.75
		$categories["method"] += 1; // 0.25 / 4.75
		$categories["proces"] += 1; // 0.25 / 4.75
	}		
	if($termFacet === "Materials") $categories["material"] += 1;
	if($termFacet === "Brand Names") {
		$categories["subject"] += 1; // 0.5 / 4.75
		$categories["material"] += 1; // 0.5 / 4.75
	}

	return $categories;
}

function classify_primitively($label, $expressions) {
	$primitives = array_fill_keys($expressions, 0);
	//Do singular matching
	if(preg_match("~^(.*)ism(e)?$~", $label)) $primitives["movement"] += 1; // 1 / 1.833333
	if(preg_match("~^(.*)istisch(e)?$~", $label)) $primitives["style"] += 1; // 1 / 1.833333
	if(preg_match("~^(.*)ing$~", $label)) $primitives["technique"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)( )?kunst$~", $label)) $primitives["discipline"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)ure(n)?$~", $label)) $primitives["technique"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)druk$~", $label)) $primitives["technique"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)erij$~", $label)) $primitives["company"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)(f|g)ie$~", $label)) $primitives["discipline"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)(loog|logen)$~", $label)) $primitives["function"] += 1; // 1 / 1.833333
	if(preg_match("~(.*)(er|ers)$~", $label)) $primitives["function"] += 1; // 1 / 1.833333
	//Do combinational matching
	if(preg_match("~^(.*)en$~", $label)) {
		$primitives["technique"] += 1; // 0.5 / 1.833333
		$primitives["material"] += 1; // 0.5 / 1.833333
	}
	if(preg_match("~(.*)(je|tje|pje|kje)$~", $label)) {
		$primitives["result"] += 1; // 0.333 / 1.833333
		$primitives["function"] += 1; // 0.333 / 1.833333
		$primitives["material"] += 1; // 0.333 / 1.833333
	}

	return $primitives;
}

function classify_all($resultTerm, $categories, $primitives) {
	$resultTerm->discipline_categorical_prob = $categories["discipline"] / $expression_count;
	$resultTerm->discipline_primitive_prob = $primitives["discipline"] / $expression_count;
	$resultTerm->discipline_combined_prob = ($categories["discipline"] + $primitives["discipline"]) / 2;
	$resultTerm->style_categorical_prob = $categories["style"] / $expression_count;
	$resultTerm->style_primitive_prob = $primitives["style"] / $expression_count;
	$resultTerm->style_combined_prob = ($categories["style"] + $primitives["style"]) / 2;
	$resultTerm->movement_categorical_prob = $categories["movement"] / $expression_count;
	$resultTerm->movement_primitive_prob = $primitives["movement"] / $expression_count;
	$resultTerm->movement_combined_prob = ($categories["movement"] + $primitives["movement"]) / 2;
	$resultTerm->proces_categorical_prob = $categories["proces"] / $expression_count;
	$resultTerm->proces_primitive_prob = $primitives["proces"] / $expression_count;
	$resultTerm->proces_combined_prob = ($categories["proces"] + $primitives["proces"]) / 2;
	$resultTerm->method_categorical_prob = $categories["method"] / $expression_count;
	$resultTerm->method_primitive_prob = $primitives["method"] / $expression_count;
	$resultTerm->method_combined_prob = ($categories["method"] + $primitives["method"]) / 2;
	$resultTerm->technique_categorical_prob = $categories["technique"] / $expression_count;
	$resultTerm->technique_primitive_prob = $primitives["technique"] / $expression_count;
	$resultTerm->technique_combined_prob = ($categories["technique"] + $primitives["technique"]) / 2;
	$resultTerm->material_categorical_prob = $categories["material"] / $expression_count;
	$resultTerm->material_primitive_prob = $primitives["material"] / $expression_count;
	$resultTerm->material_combined_prob = ($categories["material"] + $primitives["material"]) / 2;
	$resultTerm->result_categorical_prob = $categories["result"] / $expression_count;
	$resultTerm->result_primitive_prob = $primitives["result"] / $expression_count;
	$resultTerm->result_combined_prob = ($categories["result"] + $primitives["result"]) / 2;
	$resultTerm->company_categorical_prob = $categories["company"] / $expression_count;
	$resultTerm->company_primitive_prob = $primitives["company"] / $expression_count;
	$resultTerm->company_combined_prob = ($categories["company"] + $primitives["company"]) / 2;
	$resultTerm->function_categorical_prob = $categories["function"] / $expression_count;
	$resultTerm->function_primitive_prob = $primitives["function"] / $expression_count;
	$resultTerm->function_combined_prob = ($categories["function"] + $primitives["function"]) / 2;
	$resultTerm->exposure_categorical_prob = $categories["exposure"] / $expression_count;
	$resultTerm->exposure_primitive_prob = $primitives["exposure"] / $expression_count;
	$resultTerm->exposure_combined_prob = ($categories["exposure"] + $primitives["exposure"]) / 2;
	$resultTerm->subject_categorical_prob = $categories["subject"] / $expression_count;
	$resultTerm->subject_primitive_prob = $primitives["subject"] / $expression_count;
	$resultTerm->subject_combined_prob = ($categories["subject"] + $primitives["subject"]) / 2;

	return $resultTerm;
}

function get_woordenlijst_html($term, $driver) {
	$target_url = "http://woordenlijst.org/#/?q=".$term;
	$driver->get($target_url);
	$driver->wait(0, 10);
	$driver->manage()->timeouts()->implicitlyWait(10);
	$html = "";

	try {
		$element = $driver->findElement(WebDriverBy::cssSelector('.main-container'));
		$html = $element->getAttribute('innerHTML');
	} catch (Exception\NoSuchElementException $e) {
		echo "Element not found exception for: ".$target_url."\n";
	}

	return [
		"original_html" => $html,
		"source_url" => $target_url
	];
}

function get_woordenboek_html($term) {
	$target_url = "http://www.mijnwoordenboek.nl/ww.php?woord=".urlencode($term);
	$html = file_get_contents($target_url);

	// //Term is Verba; start conjugation, with DOM from URL/file
	// $html = file_get_html($target_url);
	// $found = $html->find('div.slider-wrap', 1)->find('h2', 0)->plaintext;
	// //Verb is found
	// if(strpos($found, "Helaas, het werkwoord of de werkwoordsvorm") === false) {
	// 	$conjugations = [];
		
	// 	//Find font tag for infinitive wrapper
	// 	$infinitive_fonts = $html->find('font');
	// 	foreach ($infinitive_fonts as $font_key => $infinitive_font) {
	// 		//Verb is Dutch
	// 		if(strpos($infinitive_font->plaintext, "NL: ") !== false) {
	// 			//Find b tag in font tag for infinitive text
	// 			$infinitive = $infinitive_font->find('b', 0)->plaintext;
	// 			$conjugations[] = $infinitive;
	// 			//Find all td's for conjugations
	// 			foreach($html->find('td') as $element) {
	// 				//Not the conjugation type, but the conjugation it self
	// 				if(!$element->find('i.icon-question-sign')) {
	// 					//Trim person related stuff, like Hebben/Zijn as hulpwerkwoord (Person attributions, order is important because of ambiguity)
	// 					$verba_regex = '~(dat )?(ik|jij|hij|wij|jullie|zij)( )?(hebben|hebt|heb|heeft|hadden|had|zal|zult|zullen|zouden|zou|was|waren)?( )?(?<verba>.*)( hebben)?~';
	// 					if (preg_match_all($verba_regex, $element->plaintext, $verba_matches)) {
	// 						foreach($verba_matches["verba"] as $verba_match) {
	// 							if(strpos($verba_match, "Vervoeg zoals") === false) {
	// 								$conjugation = trim($verba_match); //Trimmed conjugation
	// 								$conjugation = str_replace(" hebben", "", $conjugation);
	// 								$conjugations[] = $conjugation;
	// 							}
	// 						}						
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}		

	// 	$conjugations = array_unique($conjugations);
	// 	sort($conjugations);
	// 	return $conjugations;
	// }

	return [
		"original_html" => $html,
		"source_url" => $target_url
	];
}

function get_wiki_pages($term) {
	$pages_text = [];

	//Get pages links, for term
	$json_links = file_get_contents("https://nl.wikipedia.org/w/api.php?action=query&format=json&titles=".urlencode($term)."&generator=links");
	$links = json_decode($json_links, true);

	//Query gives page links, for term
	if( isset($links["query"]["pages"]) ) {
		//Loop all pages links
		foreach($links["query"]["pages"] as $page) {
			if(isset($page["pageid"])) {
				//Get the page data, with the current page ID
				$pageurl = "https://nl.wikipedia.org/w/api.php?action=parse&prop=text&pageid=".$page["pageid"]."&format=json";
				$pagedata = file_get_contents($pageurl);
				$pagedata = json_decode($pagedata, true);
				//Parsing is fine
				if( isset($pagedata["parse"]) ) {
					$pageparsed = $pagedata["parse"];
					//HTML text exists
					if( isset($pageparsed["text"]["*"]) ) {
						$pagehtml = $pageparsed["text"]["*"];
						$pagehtml = str_replace( '<', ' <', $pagehtml);
						$pagestripped = strip_tags($pagehtml);
						$pagestripped = str_replace( '  ', ' ', $pagestripped);
						$pagestripped = preg_replace("~(\[(.*)\])~", "", $pagestripped);
						$pagestripped = str_replace([") ", " ("], " ", $pagestripped);
						$pagestripped = str_replace([";", ":", ")", "(", " - "], "", $pagestripped);

						$pages_text[] = [
							"page_id" => $page["pageid"],
							"page_url" => $pageurl;
							"page_text" => trim($pagestripped)
						];
					}
				}
			}
		}
	}

	return $pages_text;
}
