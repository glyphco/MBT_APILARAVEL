<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendsTable extends Migration
{
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('friends');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('friends', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('friend_id');
            $table->integer('user_accepted')->default(0);
            $table->integer('friend_accepted')->default(0);
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
        Schema::dropIfExists('friends');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

}
