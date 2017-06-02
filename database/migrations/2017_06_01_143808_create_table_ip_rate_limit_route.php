<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIpRateLimitRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_rate_limit_routes', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('ip',255);
            $table->text("route");
            $table->boolean('active')->default(true);
            $table->enum('http_method', array('GET', 'HEAD','POST', 'PUT', 'DELETE', 'TRACE', 'CONNECT', 'OPTIONS', 'PATCH'));
            $table->bigInteger("rate_limit")->unsigned()->default(0);
            $table->bigInteger("rate_limit_decay")->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ip_rate_limit_routes');
    }
}
