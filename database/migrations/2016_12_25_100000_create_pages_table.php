<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('pages');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60);
            $table->string('slug', 60)->nullable()->default(null);

            $table->string('street_address')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('postalcode')->nullable()->default(null);
            $table->string('lat')->nullable()->default(null);
            $table->string('lng')->nullable()->default(null);
            $table->string('phone')->nullable()->default(null);
            $table->string('email')->nullable()->default(null);
            $table->boolean('participant')->default(0);
            $table->boolean('production')->default(0);
            $table->boolean('canhavemembers')->default(0);
            $table->boolean('canbeamember')->default(0);
            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);

            $table->integer('speciality_id')->unsigned()->nullable()->default(null);
            $table->string('tagline', 25)->nullable();
            $table->string('summary', 140)->nullable();
            $table->text('description')->nullable();

            $table->string('imageurl')->nullable();
            $table->string('backgroundurl')->nullable();

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('speciality_id')->references('id')->on('pagespecialities')->onUpdate('cascade')->onDelete('set null');

        });
        /*Spatial Column*/
        DB::statement('ALTER TABLE pages ADD location POINT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('pages');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
