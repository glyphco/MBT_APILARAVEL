<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventvenueCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('eventvenue_categories');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('eventvenue_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('eventvenue_id')->unsigned();
            $table->integer('category_id')->unsigned()->nullable();
            $table->integer('subcategory_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('eventvenue_id')->references('id')->on('event_venues')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('eventvenue_categories');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
