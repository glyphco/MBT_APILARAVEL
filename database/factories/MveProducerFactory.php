<?php

$factory->define('App\Models\MveProducer', function (Faker\Generator $faker) {
    static $password;

    $producer_pages = App\Models\Page::wherehas('eventroles', function ($q) {
        $q->where('eventrole_id', 2);
    })
        ->pluck('id')->toArray();

    return [
        'name'      => $faker->catchPhrase,
        'info'      => $faker->text($maxNbChars = 100),
        'page_id'   => $faker->optional()->randomElement($producer_pages),
        'public'    => 1,
        'confirmed' => 1,
    ];

});
