<?php

namespace Facebook\WebDriver;

function get_woordenlijst_table($term, $driver) {
	$target_url = "http://woordenlijst.org/#/?q=".$term;
	$driver->get($target_url);
	$driver->manage()->timeouts()->implicitlyWait = 10;
	try {
		$element = $driver->findElement(WebDriverBy::cssSelector('table.pos-listing-table'));
		var_dump($element);
	} catch (Exception\NoSuchElementException $e) {
		echo "Element not found exception for: ".$target_url."\n";
	}
}

