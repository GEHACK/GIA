<?php

use App\Misc\DatabaseHelpers\Blueprint;
use App\Misc\DatabaseHelpers\Schema;
use Illuminate\Database\Migrations\Migration;

class AddRoomTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("rooms", function (Blueprint $table) {
            $table->guid();
            $table->guid("contest_id");
            $table->string("name");
            $table->unsignedInteger("rows");
            $table->unsignedInteger("columns");

            $table->timestamps();

            $table->foreign("contest_id")->references("guid")->on("contests")->onDelete("restrict");
        });

        Schema::create("room_deployment", function (Blueprint $table) {
            $table->guid("room_id");
            $table->guid("deployment_id");

            $table->decimal('numerator', 16, 8);
            $table->decimal('denominator', 16, 8);

            $table->foreign("room_id")->references("guid")->on("rooms")->onDelete("cascade");
            $table->foreign("deployment_id")->references("id")->on("deployments")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("deployment_room");
        Schema::dropIfExists("rooms");
    }
}
