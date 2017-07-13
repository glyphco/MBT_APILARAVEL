<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMveProducersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mve_producers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mve_id')->unsigned();
            $table->string('name');
            $table->string('info')->nullable();
            $table->integer('page_id')->unsigned()->nullable();

            $table->integer('order')->unsigned()->default(0);

            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('page_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('mve_id')->references('id')->on('mves')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mve_producers');
    }

}
