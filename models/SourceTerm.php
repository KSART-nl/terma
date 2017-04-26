<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class SourceTerm extends Eloquent {

	protected $fillable = ['term'];
	protected $connection = "aat_nl_terms";
	protected $table = "label1";

}