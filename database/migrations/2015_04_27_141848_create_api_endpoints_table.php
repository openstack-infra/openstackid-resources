<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiEndpointsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_endpoints', function(Blueprint $table)
		{
			$table->bigIncrements('id');
            $table->boolean('active')->default(true);
            $table->boolean('allow_cors')->default(true);
            $table->boolean('allow_credentials')->default(true);
            $table->text('description')->nullable();
            $table->string('name',255)->unique();
            $table->timestamps();
            $table->text("route");
            $table->enum('http_method', array('GET', 'HEAD','POST','PUT','DELETE','TRACE','CONNECT','OPTIONS','PATCH'));
            $table->bigInteger("rate_limit")->unsigned()->nullable();
            //FK
            $table->bigInteger("api_id")->unsigned();
            $table->index('api_id');
            $table->foreign('api_id')
                ->references('id')
                ->on('apis')
                ->onDelete('cascade')
                ->onUpdate('no action');
		});

        Schema::create('endpoint_api_scopes', function($table)
        {
            $table->timestamps();

            // FK 1
            $table->bigInteger("api_endpoint_id")->unsigned();
            $table->index('api_endpoint_id');
            $table->foreign('api_endpoint_id')
                ->references('id')
                ->on('api_endpoints')
                ->onDelete('cascade')
                ->onUpdate('no action');;
            // FK 2
            $table->bigInteger("scope_id")->unsigned();
            $table->index('scope_id');
            $table->foreign('scope_id')
                ->references('id')
                ->on('api_scopes')
                ->onDelete('cascade')
                ->onUpdate('no action');;
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('endpoint_api_scopes', function($table)
        {
            $table->dropForeign('api_endpoint_id');
        });

        Schema::table('endpoint_api_scopes', function($table)
        {
            $table->dropForeign('scope_id');
        });

        Schema::dropIfExists('endpoint_api_scopes');

        Schema::table('api_endpoints', function($table)
        {
            $table->dropForeign('api_id');
        });
		Schema::drop('api_endpoints');
	}

}
