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

function get_woordenlijst_table($term, $driver) {

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

