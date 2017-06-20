<?php

/*
	Alfa characters: abcdefghijklmnopqrstuvwxyz
	Alfa diacritic characters: àáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżž
	Alfa ligature characters: æœĳß
	Puntuation characters: '- 
*/

function Ortography($term) {

	$allowed_charset = "abcdefghijklmnopqrstuvwxyzàáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżžæœĳß'-0123456789 ";
	$term = escapeshellcmd(strtolower($term));

	$matches_charset = preg_match("/[".$allowed_charset."]/", $term);

	if($matches_charset) {
		return $term;
	} else {
		return false;
	}
}
