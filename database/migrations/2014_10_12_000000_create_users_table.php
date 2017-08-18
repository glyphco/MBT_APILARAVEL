<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('username')->unique()->nullable()->default(null);
            $table->string('email')->nullable();
            $table->string('password', 60)->nullable();
            $table->bigInteger('facebook_id')->unique()->nullable();
            $table->string('google_id', 30)->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->string('imageurl')->nullable()->default(null);
            $table->string('backgroundurl')->nullable()->default(null);
            $table->string('slug', 60)->nullable();

            $table->string('street_address')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('postalcode')->nullable()->default(null);

            $table->string('locationname')->nullable()->default(null);
            $table->string('sublocationname')->nullable()->default(null);
            $table->decimal('lat', 10, 8)->nullable()->default(null);
            $table->decimal('lng', 11, 8)->nullable()->default(null);
            $table->string('local_tz')->nullable()->default(null);

            $table->integer('privacyevents')->unsigned()->default(2);
            $table->integer('privacylikes')->unsigned()->default(2);
            $table->integer('privacypyf')->unsigned()->default(2);

            $table->boolean('confirmed')->default(0);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->timestamp('banned_until')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        /*Spatial Column*/
        DB::statement('ALTER TABLE users ADD location POINT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    }
}
