<?php

$factory->define('App\Models\EventVenueShow', function (Faker\Generator $faker) {

    $eventvenueshowpage_pages = App\Models\Showpage::pluck('id')->toArray();

    return [

        'showpage_id' => $faker->randomElement($eventvenueshowpage_pages),
    ];

});
