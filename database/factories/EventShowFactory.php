<?php

$factory->define('App\Models\EventShow', function (Faker\Generator $faker) {
    static $password;

    $eventshowpage_pages = App\Models\Showpage::pluck('id')->toArray();

    return [
        'name'         => $faker->catchPhrase,
        'info'         => $faker->text($maxNbChars = 100),
        'private_info' => 'private_info',
        'showpage_id'  => $faker->optional()->randomElement($eventshowpage_pages),
    ];

});
