<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Association extends Eloquent {

	protected $connection = "result_terms";
	protected $table = "associations";

}