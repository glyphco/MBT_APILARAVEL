<?php

$factory->define('App\Models\Participant', function (Faker\Generator $faker) {
    static $password;

    $lat = $faker->latitude($min = -90, $max = 90);
    $lng = $faker->longitude($min = -180, $max = 180);

//impliment usernames from actual pages, but dont use the IDs sometimes
    //$page_id  = $faker->optional()->randomElement([1, 3, 5, 7, 9]);
    //$name = $faker->catchPhrase;
    //$info = $faker->text($maxNbChars = 100);
    //if ($page_id) {
    //$name = Page::find($page_id)->name;
    //$info = Page::find($page_id)->info;
    //}

    return [
        'name'         => $faker->catchPhrase,
        'info'         => $faker->text($maxNbChars = 100),
        'private_info' => 'private_info',
        'page_id'      => $faker->optional()->randomElement([1, 3, 5, 7, 9]),
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
