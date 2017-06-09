<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowtemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('showtemplates');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('showtemplates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60);
            $table->text('description')->nullable()->default(null);
            $table->string('category')->nullable()->default(null);
            $table->string('tagline', 50)->nullable()->default(null);
            $table->string('slug', 60)->nullable()->default(null);

            $table->integer('venue_id')->unsigned()->nullable()->default(null);
            $table->dateTime('start')->nullable()->default(null);
            $table->dateTime('end')->nullable()->default(null);

            $table->string('imageurl')->nullable()->default(null);
            $table->string('backgroundurl')->nullable()->default(null);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

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
        Schema::dropIfExists('showtemplates');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
