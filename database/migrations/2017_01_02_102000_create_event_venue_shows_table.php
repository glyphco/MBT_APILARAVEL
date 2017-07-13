<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventVenueShowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_venue_shows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_venue_id')->unsigned();
            $table->integer('showpage_id')->unsigned()->nullable();

            $table->integer('order')->unsigned()->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('showpage_id')->references('id')->on('showpages')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('event_venue_id')->references('id')->on('event_venues')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_venue_shows');
    }

}
