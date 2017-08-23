<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

//use Illuminate\Database\Schema\Blueprint;

class SeedCategories20170822 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $datetime = Carbon::now();

        $categories = [

            [
                'id'          => 1,
                'name'        => 'Comedy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 2,
                'name'        => 'Music',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 3,
                'name'        => 'Fun and Games',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 4,
                'name'        => 'Arts',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 5,
                'name'        => 'Sports',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 6,
                'name'        => 'Science',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
        ];

        \DB::table('categories')->truncate();
        \DB::table('categories')->insert($categories);

        $subcategories = [
            [
                'id'          => 1,
                'category_id' => 1,
                'name'        => 'Comedy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 2,
                'category_id' => 1,
                'name'        => 'Stand-up Comedy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 3,
                'category_id' => 1,
                'name'        => 'Comedic Writing',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 4,
                'category_id' => 1,
                'name'        => 'Improv Comedy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 5,
                'category_id' => 1,
                'name'        => 'Sketch Comedy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 6,
                'category_id' => 1,
                'name'        => 'Comedy Hosting',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 7,
                'category_id' => 2,
                'name'        => 'Music',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 8,
                'category_id' => 2,
                'name'        => 'Rock',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 9,
                'category_id' => 2,
                'name'        => 'Pop',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 10,
                'category_id' => 2,
                'name'        => 'Funk',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 11,
                'category_id' => 2,
                'name'        => 'Psychedelic',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 12,
                'category_id' => 2,
                'name'        => 'Emo',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 13,
                'category_id' => 2,
                'name'        => 'Pop-Punk',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 14,
                'category_id' => 2,
                'name'        => 'Acoustic',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 15,
                'category_id' => 2,
                'name'        => 'Country',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 16,
                'category_id' => 2,
                'name'        => 'Americana',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 17,
                'category_id' => 2,
                'name'        => 'Covers',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 18,
                'category_id' => 2,
                'name'        => 'Rock',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 19,
                'category_id' => 2,
                'name'        => 'Drums',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 20,
                'category_id' => 2,
                'name'        => 'Guitars',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 21,
                'category_id' => 2,
                'name'        => 'Vocals',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 22,
                'category_id' => 2,
                'name'        => 'Bass',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 23,
                'category_id' => 2,
                'name'        => 'Piano',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 24,
                'category_id' => 2,
                'name'        => 'Trumpet',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 25,
                'category_id' => 3,
                'name'        => 'Fun and Games',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 26,
                'category_id' => 3,
                'name'        => 'Pinball',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 27,
                'category_id' => 3,
                'name'        => 'Arcade',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 28,
                'category_id' => 3,
                'name'        => 'Video Games',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 29,
                'category_id' => 3,
                'name'        => 'Board Games',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 30,
                'category_id' => 4,
                'name'        => 'Arts',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 31,
                'category_id' => 4,
                'name'        => 'Theatre',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 32,
                'category_id' => 4,
                'name'        => 'Film and Movies',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 33,
                'category_id' => 4,
                'name'        => 'Visual Arts',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 34,
                'category_id' => 5,
                'name'        => 'Sports',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 35,
                'category_id' => 5,
                'name'        => 'Biking',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 36,
                'category_id' => 5,
                'name'        => 'Roller Derby',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 37,
                'category_id' => 5,
                'name'        => 'Baseball',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 38,
                'category_id' => 5,
                'name'        => 'Basketball',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 39,
                'category_id' => 5,
                'name'        => 'Softball',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 40,
                'category_id' => 6,
                'name'        => 'Science',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 41,
                'category_id' => 6,
                'name'        => 'Astronomy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 42,
                'category_id' => 6,
                'name'        => 'Biology',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 43,
                'category_id' => 6,
                'name'        => 'History',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],
            [
                'id'          => 44,
                'category_id' => 1,
                'name'        => 'Open-Mic Comedy',
                'description' => null,
                'created_at'  => $datetime,
                'updated_at'  => $datetime,
            ],

        ];

        DB::table('subcategories')->truncate();
        DB::table('subcategories')->insert($subcategories);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
