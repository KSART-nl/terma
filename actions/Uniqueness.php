<?php

function Uniqueness($term) {

	return(!count(ResultTerm::where("term", $term)->get()));

}