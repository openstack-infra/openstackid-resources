<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableApiEndpoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_endpoints', function(Blueprint $table)
        {
            $table->dropColumn("rate_limit");
        });

        Schema::table('api_endpoints', function(Blueprint $table)
        {
            $table->bigInteger("rate_limit")->unsigned()->default(0);
            $table->bigInteger("rate_limit_decay")->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_endpoints', function(Blueprint $table)
        {
            $table->dropColumn('rate_limit_decay');
            $table->dropColumn("rate_limit");
        });

        Schema::table('api_endpoints', function(Blueprint $table)
        {
            $table->bigInteger("rate_limit")->unsigned()->nullable();
        });
    }
}
