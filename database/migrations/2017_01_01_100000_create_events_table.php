<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('events');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start')->nullable()->default(null);
            $table->dateTime('end')->nullable()->default(null);
//shows
            $table->text('showjson')->nullable()->default(null);

//pricing
            $table->integer('price')->unsigned()->nullable();
            $table->integer('pricemin')->unsigned()->nullable();
            $table->integer('pricemax')->unsigned()->nullable();
            $table->string('pricedescription')->nullable();
            $table->string('pricelink')->nullable();

            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->string('imageurl')->nullable();
            $table->string('backgroundurl')->nullable();

        });
        /*Spatial Column*/
        DB::statement('ALTER TABLE events ADD location POINT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }

}
