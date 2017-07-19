<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('show_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('show_id')->unsigned();
            $table->string('name');
            $table->string('info')->nullable();
            $table->string('private_info')->nullable();
            $table->integer('page_id')->unsigned()->nullable();

            $table->integer('order')->unsigned()->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('page_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('show_id')->references('id')->on('shows')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('show_participants');
    }

}
