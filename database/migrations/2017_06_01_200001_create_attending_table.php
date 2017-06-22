<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendingTable extends Migration
{
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('attending');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('attending', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('eventvenue_id');
            $table->integer('rank')->nullable()->default(0);
            $table->softDeletes();
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('attending');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

}
