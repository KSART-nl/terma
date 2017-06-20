<?php

function UniquenessAction($term) {

	return(!count(ResultTerm::where("term", $term)->get()));

}