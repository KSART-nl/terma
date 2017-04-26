<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Model;

set_time_limit(60000000);
ini_set('max_execution_time', 60000000);

//Seeds
Model::unguard();

//Migrations and Seeds
if(!Capsule::schema("terma")->hasTable('terms')) {
	Capsule::schema("terma")->create("terms", function($table) {
		$table->increments('id');

		$table->text('term');
		$table->text('context');

		$table->timestamps();
	});
}


Model::reguard();