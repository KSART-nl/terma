<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Term extends Eloquent {

	protected $fillable = ['term'];
	protected $connection = "terma";
	protected $table = "terms";

}