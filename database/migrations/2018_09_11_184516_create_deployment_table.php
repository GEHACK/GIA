<?php

use App\Misc\DatabaseHelpers\Blueprint;
use App\Misc\DatabaseHelpers\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateDeploymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->char('id',32)->primary();

            $table->unsignedInteger("userid")->nullable();

            $table->string('proxy_ip', 15)->nullable();
            $table->string('ip', 15);

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
        Schema::dropIfExists('deployments');
    }
}
