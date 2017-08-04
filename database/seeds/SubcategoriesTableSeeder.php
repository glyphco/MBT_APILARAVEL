<?php

use Illuminate\Database\Seeder;

class SubcategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('subcategories')->delete();
        
        \DB::table('subcategories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'category_id' => 1,
                'name' => 'Comedy',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            1 => 
            array (
                'id' => 2,
                'category_id' => 1,
                'name' => 'Stand-up Comedy',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            2 => 
            array (
                'id' => 3,
                'category_id' => 1,
                'name' => 'Comedic Writing',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            3 => 
            array (
                'id' => 4,
                'category_id' => 1,
                'name' => 'Improv Comedy',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            4 => 
            array (
                'id' => 5,
                'category_id' => 1,
                'name' => 'Sketch Comedy',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            5 => 
            array (
                'id' => 6,
                'category_id' => 1,
                'name' => 'Comedy Hosting',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            6 => 
            array (
                'id' => 7,
                'category_id' => 2,
                'name' => 'Music',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            7 => 
            array (
                'id' => 8,
                'category_id' => 2,
                'name' => 'Rock',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            8 => 
            array (
                'id' => 9,
                'category_id' => 2,
                'name' => 'Pop',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            9 => 
            array (
                'id' => 10,
                'category_id' => 2,
                'name' => 'Funk',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            10 => 
            array (
                'id' => 11,
                'category_id' => 2,
                'name' => 'Psychedelic',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            11 => 
            array (
                'id' => 12,
                'category_id' => 2,
                'name' => 'Emo',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            12 => 
            array (
                'id' => 13,
                'category_id' => 2,
                'name' => 'Pop-Punk',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            13 => 
            array (
                'id' => 14,
                'category_id' => 2,
                'name' => 'Acoustic',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            14 => 
            array (
                'id' => 15,
                'category_id' => 2,
                'name' => 'Country',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            15 => 
            array (
                'id' => 16,
                'category_id' => 2,
                'name' => 'Americana',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            16 => 
            array (
                'id' => 17,
                'category_id' => 2,
                'name' => 'Covers',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            17 => 
            array (
                'id' => 18,
                'category_id' => 2,
                'name' => 'Rock Band',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            18 => 
            array (
                'id' => 19,
                'category_id' => 2,
                'name' => 'Drums',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            19 => 
            array (
                'id' => 20,
                'category_id' => 2,
                'name' => 'Guitars',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            20 => 
            array (
                'id' => 21,
                'category_id' => 2,
                'name' => 'Vocals',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            21 => 
            array (
                'id' => 22,
                'category_id' => 2,
                'name' => 'Bass',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            22 => 
            array (
                'id' => 23,
                'category_id' => 2,
                'name' => 'Piano',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            23 => 
            array (
                'id' => 24,
                'category_id' => 2,
                'name' => 'Trumpet',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            24 => 
            array (
                'id' => 25,
                'category_id' => 3,
                'name' => 'Fun and Games',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            25 => 
            array (
                'id' => 26,
                'category_id' => 3,
                'name' => 'Pinball',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            26 => 
            array (
                'id' => 27,
                'category_id' => 3,
                'name' => 'Arcade',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            27 => 
            array (
                'id' => 28,
                'category_id' => 3,
                'name' => 'Video Games',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            28 => 
            array (
                'id' => 29,
                'category_id' => 3,
                'name' => 'Board Games',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            29 => 
            array (
                'id' => 30,
                'category_id' => 4,
                'name' => 'Arts',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            30 => 
            array (
                'id' => 31,
                'category_id' => 4,
                'name' => 'Theatre',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            31 => 
            array (
                'id' => 32,
                'category_id' => 4,
                'name' => 'Film and Movies',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            32 => 
            array (
                'id' => 33,
                'category_id' => 4,
                'name' => 'Visual Arts',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            33 => 
            array (
                'id' => 34,
                'category_id' => 5,
                'name' => 'Sports',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            34 => 
            array (
                'id' => 35,
                'category_id' => 5,
                'name' => 'Biking',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            35 => 
            array (
                'id' => 36,
                'category_id' => 5,
                'name' => 'Roller Derby',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            36 => 
            array (
                'id' => 37,
                'category_id' => 5,
                'name' => 'Baseball',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            37 => 
            array (
                'id' => 38,
                'category_id' => 5,
                'name' => 'Basketball',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            38 => 
            array (
                'id' => 39,
                'category_id' => 5,
                'name' => 'Softball',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            39 => 
            array (
                'id' => 40,
                'category_id' => 6,
                'name' => 'Science',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            40 => 
            array (
                'id' => 41,
                'category_id' => 6,
                'name' => 'Astronomy',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            41 => 
            array (
                'id' => 42,
                'category_id' => 6,
                'name' => 'Biology',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            42 => 
            array (
                'id' => 43,
                'category_id' => 6,
                'name' => 'History',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
        ));
        
        
    }
}