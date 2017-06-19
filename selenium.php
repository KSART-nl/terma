<?php

namespace Facebook\WebDriver;

/*
	Looping elements gives this output:

	enkelvoud
	koe
	koe
	<span class="highlighted">koe</span>
	koe
	[<span class="ng-binding" ng-bind-html="info.hyph">koe</span>]
	koe
	meervoud
	koei·en
	koeien
	[<span class="ng-binding" ng-bind-html="info.hyph">koei·en</span>]
	koei·en
	enkelvoud verkleinvorm
	koe·tje
	koetje
	[<span class="ng-binding" ng-bind-html="info.hyph">koe·tje</span>]
	koe·tje
	meervoud verkleinvorm
	koe·tjes
	koetjes
	[<span class="ng-binding" ng-bind-html="info.hyph">koe·tjes</span>]
	koe·tjes
*/

function get_woordenlijst_nomina_table($term, $driver) {

	$target_url = "http://woordenlijst.org/#/?q=".$term;
	$driver->get($target_url);
	$driver->wait(0, 10);
	$driver->manage()->timeouts()->implicitlyWait(10);
	$woordenlijst = [];

	try {

		$elements = $driver->findElements(WebDriverBy::cssSelector('table.info-table.pos-listing-table tr td span'));		
		
		foreach ($elements as $element_key => $element) {

			switch (true) {
			    case $element_key === 2: //Example: koe
			        $woordenlijst['singular'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 9: //Example: koeien
			        $woordenlijst['plural'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 14: //Example: koeitje
			        $woordenlijst['singular_diminutive'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 19: //Example: koeitjes
			        $woordenlijst['plural_diminutive'] = $element->getAttribute('innerHTML');
			        break;
			}

		}

	} catch (Exception\NoSuchElementException $e) {
		echo "Element not found exception for: ".$target_url."\n";
	}

	var_dump($woordenlijst);
	return $woordenlijst;

}

/*
	Looping elements gives this output:

	stellende trap
	leuk
	leuk
	<span class="highlighted">leuk</span>
	leuk
	[<span class="ng-binding" ng-bind-html="info.hyph">leuk</span>]
	leuk
	verbogen stellende trap
	leu·ke
	leuke
	[<span class="ng-binding" ng-bind-html="info.hyph">leu·ke</span>]
	leu·ke
	s-vorm
	leuks
	leuks
	[<span class="ng-binding" ng-bind-html="info.hyph">leuks</span>]
	leuks
	vergrotende trap
	leu·ker
	leuker
	[<span class="ng-binding" ng-bind-html="info.hyph">leu·ker</span>]
	leu·ker
	verbogen vergrotende trap
	leu·ke·re
	leukere
	[<span class="ng-binding" ng-bind-html="info.hyph">leu·ke·re</span>]
	leu·ke·re
	overtreffende trap
	leukst
	leukst
	[<span class="ng-binding" ng-bind-html="info.hyph">leukst</span>]
	leukst
	verbogen overtreffende trap
	leuk·ste
	leukste
	[<span class="ng-binding" ng-bind-html="info.hyph">leuk·ste</span>]
	leuk·ste
*/
function get_woordenlijst_adjectiva_table($term, $driver) {

	$target_url = "http://woordenlijst.org/#/?q=".$term;
	$driver->get($target_url);
	$driver->wait(0, 10);
	$driver->manage()->timeouts()->implicitlyWait(10);
	$woordenlijst = [];

	try {

		$elements = $driver->findElements(WebDriverBy::cssSelector('table.info-table.pos-listing-table tr td span'));		
		
		foreach ($elements as $element_key => $element) {

			switch (true) {
			    case $element_key === 2: //Example: leuk
			        $woordenlijst['possitive_degree'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 9: //Example: leuke
			        $woordenlijst['declined_possitive_adjective'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 14: //Example: leuks
			        $woordenlijst['possitive_s_degree'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 19: //Example: leuker
			        $woordenlijst['comparative_degree'] = $element->getAttribute('innerHTML');
			        break;
				case $element_key === 24: //Example: leukere
			        $woordenlijst['declined_comparative_adjective'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 29: //Example: leukst
			        $woordenlijst['superlative_degree'] = $element->getAttribute('innerHTML');
			        break;
			    case $element_key === 34: //Example: leukste
			        $woordenlijst['declined_superlative_adjective'] = $element->getAttribute('innerHTML');
			        break;
			}

		}

	} catch (Exception\NoSuchElementException $e) {
		echo "Element not found exception for: ".$target_url."\n";
	}

	var_dump($woordenlijst);
	return $woordenlijst;

}

