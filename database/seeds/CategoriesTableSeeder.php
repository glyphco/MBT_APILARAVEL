<?php

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('categories')->delete();
        
        \DB::table('categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Comedy',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Music',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Fun and Games',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Arts',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Sports',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Science',
                'description' => NULL,
                'created_at' => '2017-08-03 20:32:54',
                'updated_at' => '2017-08-03 20:32:54',
            ),
        ));
        
        
    }
}