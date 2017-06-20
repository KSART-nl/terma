<?php

/*
	Alfa characters: abcdefghijklmnopqrstuvwxyz
	Alfa diacritic characters: àáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżž
	Alfa ligature characters: æœĳß
	Puntuation characters: '- 
*/

function Ortography($term) {

	$allowed_charset = "abcdefghijklmnopqrstuvwxyzàáâãäåāăąçćĉċčďđèéêëēĕėęěĝğġģĥħìíîïĩīĭıįĵķĺļľŀłñńņňŋòóôöõøōŏőŕŗřśŝşšţťŧùúûüũůūŭűųŵýÿŷźżžæœĳß'-0123456789 ";
	return preg_match("/[".$allowed_charset."]/", $term);
}
