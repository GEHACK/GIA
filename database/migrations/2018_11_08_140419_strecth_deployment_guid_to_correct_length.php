<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StrecthDeploymentGuidToCorrectLength extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::drop('room_deployment');

        Schema::table('execJobs', function (Blueprint $table) {
            $table->dropForeign('execjobs_deployment_id_foreign');
        });

        \DB::statement("ALTER TABLE execJobs MODIFY deployment_id char(36) NOT NULL");
        \DB::statement("ALTER TABLE deployments MODIFY guid char(36) NOT NULL");

        Schema::table('execJobs', function (Blueprint $table) {
            $table->foreign('deployment_id')->references('guid')->on('deployments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
