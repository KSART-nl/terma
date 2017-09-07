<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class PosTag extends Eloquent {

	protected $connection = "result_terms";
	protected $table = "postags";

}