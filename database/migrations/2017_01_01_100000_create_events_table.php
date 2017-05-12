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
            $table->string('name');
            $table->text('description')->nullable();
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
            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->string('imageurl')->nullable();
            $table->string('backgroundurl')->nullable();

            $table->foreign('venue_id')->references('id')->on('venues')->onUpdate('cascade')->onDelete('set null');
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
