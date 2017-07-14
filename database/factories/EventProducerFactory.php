<?php

$factory->define('App\Models\EventProducer', function (Faker\Generator $faker) {
    static $password;

    $producer_pages = App\Models\Page::where('production', 1)
        ->pluck('id')->toArray();

    return [
        'name'         => $faker->catchPhrase,
        'info'         => $faker->text($maxNbChars = 100),
        'private_info' => 'private_info',
        'page_id'      => $faker->optional()->randomElement($producer_pages),
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
