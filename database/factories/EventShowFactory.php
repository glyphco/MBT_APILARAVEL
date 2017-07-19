<?php

$factory->define('App\Models\EventShow', function (Faker\Generator $faker) {

    $eventshows = App\Models\Show::pluck('id')->toArray();

    return [

        'show_id' => $faker->randomElement($eventshows),
    ];

});
