<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class WikiPage extends Eloquent {

	protected $connection = "result_terms";
	protected $table = "wikis";

}