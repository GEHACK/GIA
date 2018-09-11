<?php

use App\Misc\DatabaseHelpers\Blueprint;
use App\Misc\DatabaseHelpers\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateContestsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('contests', function (Blueprint $table) {
            $table->guid();
            $table->unsignedInteger('cid');
            $table->string("hash");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('contests');
    }
}
