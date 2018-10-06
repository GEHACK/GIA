<?php

use App\Misc\DatabaseHelpers\Blueprint;
use App\Misc\DatabaseHelpers\Schema;
use Illuminate\Database\Migrations\Migration;

class AddExecScriptTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("execJobs", function(Blueprint $table) {
            $table->guid();
            $table->char("deployment_id", 32);
            $table->enum("type", ["percentage", "absolute"]);
            $table->enum("status", ["submitted", "running", "finished", "terminated"]);
            $table->string("value", 16);
            $table->text("result");
            $table->timestamps();

            $table->foreign("deployment_id")->references("id")->on("deployments")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("execJobs");
    }
}
