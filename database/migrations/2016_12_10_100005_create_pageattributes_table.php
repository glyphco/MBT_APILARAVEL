<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageattributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('pageattributes');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::create('pageattributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();

        });

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $datetime = Carbon::now();

        $data = [

            [
                'id'          => '1',
                'name'        => 'bookable',
                'description' => 'Can preform or otherwise be a participant of an event (solo artist, band, sports team)',
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],

            [
                'id'          => '2',
                'name'        => 'production',
                'description' => 'Can be listed as a production credit (Producer, Production Company)',
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],

            [
                'id'          => '3',
                'name'        => 'group',
                'description' => 'Can have other pages connected to this page (Band, sports team)',
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],

            [
                'id'          => '4',
                'name'        => 'show',
                'description' => 'A Repeatable Show or Major Event (Comedians You Should Know, RiotFest)',
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
        ];

        DB::table('pageattributes')->truncate();
        DB::table('pageattributes')->insert($data);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('pageattributes');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
