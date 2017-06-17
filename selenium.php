<?php

namespace Facebook\WebDriver;

function get_woordenlijst_table($term, $driver) {
	$target_url = "http://woordenlijst.org/#/?q=".$term;
	$driver->get($target_url);
	$driver->wait(0, 10);
	$driver->manage()->timeouts()->implicitlyWait = 10;
	try {
		$elements = $driver->findElement(WebDriverBy::cssSelector('table.info-table.pos-listing-table tr td span'));
		
		foreach ($elements as $element) echo $element->getAttribute('innerHTML');


	} catch (Exception\NoSuchElementException $e) {
		echo "Element not found exception for: ".$target_url."\n";
	}
}

