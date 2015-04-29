<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiScopesTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_scopes', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name', 512);
			$table->string('short_description', 512);
			$table->text('description');
			$table->boolean('active')->default(true);
			$table->boolean('default')->default(false);
			$table->boolean('system')->default(false);
			$table->timestamps();
			// FK
			$table->bigInteger("api_id")->unsigned()->nullable();
			$table->index('api_id');
			$table->foreign('api_id')
				->references('id')
				->on('apis')
				->onDelete('cascade')
				->onUpdate('no action');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_scopes', function ($table) {
			$table->dropForeign('api_id');
		});

		Schema::drop('api_scopes');
	}

}
