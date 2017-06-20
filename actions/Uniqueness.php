<?php

function Uniqueness($term) {

	return(!count(Term::where("term", $term)->get()));

}