<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class ResultTerm extends Eloquent {

	protected $fillable = ['term'];
	protected $connection = "terma";
	protected $table = "terms";

}