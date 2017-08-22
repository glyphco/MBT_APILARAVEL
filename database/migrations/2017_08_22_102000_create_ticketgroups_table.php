<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketgroups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->unsigned();

            $table->dateTime('UTC_open')->nullable()->default(null);
            $table->dateTime('UTC_cutoff')->nullable()->default(null);
            $table->dateTime('local_open')->nullable()->default(null);
            $table->dateTime('local_cutoff')->nullable()->default(null);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticketgroups');
    }

}
