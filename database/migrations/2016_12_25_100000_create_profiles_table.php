<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // Schema::dropIfExists('profiles');
        // DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        // Schema::create('profiles', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('name', 60);
        //     $table->string('slug', 60)->nullable()->default(null);
        //     $table->string('category')->nullable()->default(null);
        //     $table->string('street_address')->nullable()->default(null);
        //     $table->string('city')->nullable()->default(null);
        //     $table->string('state')->nullable()->default(null);
        //     $table->string('postalcode')->nullable()->default(null);
        //     $table->string('lat')->nullable()->default(null);
        //     $table->string('lng')->nullable()->default(null);
        //     $table->string('phone')->nullable()->default(null);
        //     $table->string('email')->nullable()->default(null);
        //     $table->boolean('participant')->default(0);
        //     $table->boolean('production')->default(0);
        //     $table->boolean('canhavemembers')->default(0);
        //     $table->boolean('canbeamember')->default(0);
        //     $table->boolean('public')->default(0);
        //     $table->boolean('confirmed')->default(0);

        //     $table->string('tagline', 50)->nullable();
        //     $table->text('description')->nullable();
        //     $table->string('imageurl')->nullable();
        //     $table->string('backgroundurl')->nullable();

        //     $table->timestamps();
        //     $table->unsignedInteger('created_by')->nullable()->default(null);
        //     $table->unsignedInteger('updated_by')->nullable()->default(null);

        // });
        // /*Spatial Column*/
        // DB::statement('ALTER TABLE profiles ADD location POINT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('profiles');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
