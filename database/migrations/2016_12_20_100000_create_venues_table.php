<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('venues');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('venues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug', 60)->nullable()->default(null);
            $table->string('category')->nullable()->default(null);
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('postalcode');
            $table->string('neighborhood')->nullable()->default(null);

            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('local_tz');

            $table->string('phone')->nullable()->default(null);
            $table->string('email')->nullable()->default(null);
            $table->string('tagline', 50)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('website')->nullable()->default(null);
            $table->string('imageurl')->nullable()->default(null);
            $table->string('backgroundurl')->nullable()->default(null);

            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

        });

        /*Spatial Column*/
        DB::statement('ALTER TABLE venues ADD location POINT');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('venues');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
