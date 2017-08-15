<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Model;

set_time_limit(60000000);
ini_set('max_execution_time', 60000000);

//Seeds
Model::unguard();

//Migrations and Seeds
if(!Capsule::schema("result_terms")->hasTable('terms')) {
	Capsule::schema("result_terms")->create("terms", function($table) {
		$table->increments('id');

		$table->text('label');
		$table->text('prefLabel');
		$table->text('altLabel');
		$table->text('parentString');
		$table->text('scopeNote');	
		
		$table->timestamps();
	});
}

if(!Capsule::schema("result_terms")->hasTable('adjectivas')) {
	Capsule::schema("result_terms")->create("adjectivas", function($table) {
		$table->increments('id');

		//Stellende trap
		$table->text('possitive_degree'); //Example: Duur (DB default)
		$table->text('declined_possitive_adjective'); //Example: Dure (UI default)
		$table->text('possitive_s_degree'); //Example: Duurs
		//Vergrotende trap
		$table->text('comparative_degree'); //Example: Duurder
		$table->text('declined_comparative_adjective'); //Example: Duurdere
		//Overtreffende trap
		$table->text('superlative_degree'); //Example: Duurst
		$table->text('declined_superlative_adjective'); //Example: Duurste

		$table->timestamps();
	});
}

if(!Capsule::schema("result_terms")->hasTable('generas')) {
	Capsule::schema("result_terms")->create("generas", function($table) {
		$table->increments('id');

		//Mannelijk
		$table->text('singular_masculine'); //Example: Kunstschilder
		$table->text('singular_masculine_diminutive'); //Example: Kunstschildertje
		$table->text('plural_masculine'); //Example: Kunstschilders (Default)
		$table->text('plural_masculine_diminutive'); //Example: Kunstschildertjes

		//Vrouwelijk
		$table->text('singular_feminine'); //Example: Kunstschilderes
		$table->text('singular_feminine_diminutive'); //Example: Kunstschilderesje
		$table->text('plural_feminine'); //Example: Kunstschilderesen
		$table->text('plural_feminine_diminutive'); //Example: Kunstschilderesjes

		$table->timestamps();
	});
}

if(!Capsule::schema("result_terms")->hasTable('nominas')) {
	Capsule::schema("result_terms")->create("nominas", function($table) {
		$table->increments('id');

		$table->text('singular'); //Example: Schilderij (Default)
		$table->text('singular_diminutive'); //Example: Schilderijtje
		$table->text('plural'); //Example: Schilderijen
		$table->text('plural_diminutive'); //Example: Schilderijtjes

		$table->timestamps();
	});
}

if(!Capsule::schema("result_terms")->hasTable('verbas')) {
	Capsule::schema("result_terms")->create("verbas", function($table) {
		$table->increments('id');

		$table->text('infinitive'); //Infinitief
		$table->text('imperative'); //Gebiedende wijs		

		$table->text('first_singular_person');
		$table->text('first_plural_person');

		$table->text('second_singular_person');
		$table->text('second_plural_person');

		$table->text('third_singular_person');
		$table->text('third_plural_person');

		$table->timestamps();
	});
}

Model::reguard();