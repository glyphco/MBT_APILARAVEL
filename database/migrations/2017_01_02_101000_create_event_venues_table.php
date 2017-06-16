<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventVenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_venues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->unsigned();
            $table->integer('venue_id')->unsigned()->nullable();
            $table->string('venue_name');
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('postalcode');
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('venue_tagline', 50)->nullable();

            $table->dateTime('start');
            $table->dateTime('end')->nullable()->default(null);

            $table->string('info')->nullable();
            $table->string('private_info')->nullable();

            $table->integer('order')->unsigned()->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('venue_id')->references('id')->on('venues')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
        /*Spatial Column*/
        DB::statement('ALTER TABLE event_venues ADD location POINT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_venues');
    }

}
