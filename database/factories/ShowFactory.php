<?php

$factory->define('App\Models\Show', function (Faker\Generator $faker) {
    static $password;

    $show_pages = App\Models\Page::wherehas('eventroles', function ($q) {
        $q->where('eventrole_id', 3);
    })
        ->pluck('id')->toArray();

    return [
        'name'         => $faker->catchPhrase,
        'info'         => $faker->text($maxNbChars = 100),
        'private_info' => 'private_info',
        'page_id'      => $faker->optional()->randomElement($show_pages),
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
