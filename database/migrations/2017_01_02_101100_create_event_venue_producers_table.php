<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventVenueProducersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_venue_producers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_venue_id')->unsigned();
            $table->string('name');
            $table->string('info')->nullable();
            $table->string('private_info')->nullable();
            $table->integer('page_id')->unsigned()->nullable();

            $table->integer('order')->unsigned()->default(0);

            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('page_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('set null');
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
        Schema::dropIfExists('event_venue_producers');
    }

}
