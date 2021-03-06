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
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
//MVE
            $table->integer('mve_id')->unsigned()->nullable()->default(null);

            $table->string('name');
            $table->text('description')->nullable();
//JSON
            $table->json('showsjson');
            $table->json('categoriesjson');
            $table->json('participantsjson');
//venue stuff
            $table->integer('venue_id')->unsigned()->nullable();
            $table->string('venue_name')->nullable()->default(null);
            $table->string('venue_imageurl')->nullable();

            $table->string('street_address')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('postalcode')->nullable()->default(null);
            $table->decimal('lat', 10, 8)->nullable()->default(null);
            $table->decimal('lng', 11, 8)->nullable()->default(null);
            $table->string('local_tz');
            $table->string('venue_tagline', 50)->nullable();
//pricing
            $table->integer('price')->unsigned()->nullable();
            $table->integer('pricemin')->unsigned()->nullable();
            $table->integer('pricemax')->unsigned()->nullable();
            $table->string('pricedescription')->nullable();
            $table->string('pricelink')->nullable();
//ages
            $table->integer('ages')->unsigned()->nullable();
//times
            $table->dateTime('UTC_start');
            $table->dateTime('UTC_end')->nullable()->default(null);
            $table->dateTime('local_start');
            $table->dateTime('local_end')->nullable()->default(null);
//info
            $table->string('info')->nullable();
            $table->string('private_info')->nullable();
            $table->string('imageurl')->nullable();
            $table->string('backgroundurl')->nullable();
//we dont use ordering ...yet
            $table->integer('order')->unsigned()->default(0);
//confirmations
            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);
//internals
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('venue_id')->references('id')->on('venues')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('mve_id')->references('id')->on('mves')->onDelete('set null');
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
