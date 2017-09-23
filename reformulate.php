<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('memory_limit', '-1');

require 'vendor/autoload.php';
require 'config/env.php';
require 'start.php';
require "apriori.php";

$expressions = ["discipline","style","movement","proces","method","technique","material","result","company","function","exposure","subject"];
$art_indicators = ["kunst", "creatief", "creativiteit", "cultuur"];

$reformulate_classifications = false;
$reformulate_kunstgehalts = false;
$reformulate_associations = false;
$reformulate_flexions = true;

if($reformulate_classifications) {
	$terms = ResultTerm::all();
	foreach($terms as $term) {

		$prob_values = [];
		//Find highest combined value
		foreach($expressions as $expression) {
			$current_prob = $expression."_combined_prob";
			$prob_values[$expression] = $term->$current_prob;
		}
		$highest_prob = max($prob_values);
		$likely_expressions = array_keys($prob_values, $highest_prob);

		$term->classified_expressions = implode(",", $likely_expressions);
		$term->save();
	}
	echo "Done reformulating terms";
}

if($reformulate_kunstgehalts || $reformulate_associations) {
	$wikipages = WikiPage::orderBy("term", "ASC")->get(); //take(500)->get();
	$current_term = "";
	$current_pages_text = "";
	$wikipages_count = count($wikipages);
	$is_end = false;
	$art_indicator_count = 0;
	foreach ($wikipages as $wikipage_key => $wikipage) {

		$current_term = $wikipages[$wikipage_key];
		echo $current_term->term.PHP_EOL;
		
		//Is not last from total array
		if($wikipage_key !== $wikipages_count - 1) {
			$is_end = false;
			$next_term = $wikipages[$wikipage_key+1];
		} else {
			$is_end = true;
		}
		//echo "Is end: ".($is_end ? "Yes":"No").PHP_EOL;

		$current_pages_text .= $wikipages[$wikipage_key]->page_text;

		//if(!$is_end) {
			//Last of current term, so do some math
			if($current_term->term !== $next_term->term || $is_end) {
				//echo $current_pages_text.PHP_EOL;
				foreach ($art_indicators as $art_indicator) {
					$art_indicator_count += substr_count($current_pages_text, $art_indicator);
				}

				if($reformulate_kunstgehalts) {
					echo "Indicator count: ".$art_indicator_count.PHP_EOL.PHP_EOL;
					$kunstgehalt = new Kunstgehalt();
					$kunstgehalt->term = $current_term->term;
					$kunstgehalt->kunstcount = $art_indicator_count;
					$kunstgehalt->kunstgehalt = 0;
					$kunstgehalt->save();
				}
				$art_indicator_count = 0;			

				if($reformulate_associations) {
					//Transactions from Text file (file) or Text string (text)
					$data = ["text" => $current_pages_text];
					$apriori = new Apriori($data);
					$associations = $apriori::get_associations();
					foreach($associations as $association) {
						if($association["confidence"] > 0.5) {
							
							$associationRecord = new Association();
							$associationRecord->start_term = $association[0];
							$associationRecord->end_term = $association[1];
							$associationRecord->containment = $association["containment"];
							$associationRecord->support = $association["support"];
							$associationRecord->appearance = $association["appearance"];
							$associationRecord->confidence = $association["confidence"];
							$associationRecord->save();
						}
					}
				}
				$current_pages_text = "";

			}
		//}

	}
	//echo "Done reformulating wikis";
}

if($reformulate_flexions) {
	$flexions = Flexion::all();
	foreach ($flexions as $flexion_key => $flexion) {
		if(strpos($flexion->source_url, "www.mijnwoordenboek.nl")){

			echo $flexion->term.': woordenboek - '.$flexion->source_url.PHP_EOL;
			$html = str_get_html($flexion->original_html);

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

				$conjugations = array_unique($conjugations);
				sort($conjugations);
				var_dump($conjugations);
			}

		} else if (strpos($flexion->source_url, "woordenlijst.org")) {

			echo $flexion->term.': woordenlijst - '.$flexion->source_url.PHP_EOL;
			$html = str_get_html($flexion->original_html);

			var_dump($flexion->original_html);
		}

		
	}
	
}