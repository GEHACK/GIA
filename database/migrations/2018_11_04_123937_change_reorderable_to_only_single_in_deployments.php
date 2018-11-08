<?php

use App\Misc\DatabaseHelpers\Blueprint;
use App\Misc\DatabaseHelpers\Schema;
use Illuminate\Database\Migrations\Migration;

class ChangeReorderableToOnlySingleInDeployments extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('deployments', function (Blueprint $table) {
            $table->decimal('denominator', 16, 8)->after('ip');
            $table->decimal('numerator', 16, 8)->after('ip');
            $table->guid("room_id")->after('ip')->nullable();

            $table->foreign("room_id")->references("guid")->on("rooms")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropColumn('denominator');
            $table->dropColumn('numerator');
            $table->dropColumn("room_id");
        });
    }
}
