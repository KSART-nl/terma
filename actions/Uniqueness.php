<?php

require 'vendor/autoload.php';

function Uniqueness($term) {

	return(!count(Term::where("term", $term)->get()));

}