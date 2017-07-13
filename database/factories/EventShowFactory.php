<?php

$factory->define('App\Models\EventShow', function (Faker\Generator $faker) {

    $eventshowpage_pages = App\Models\Showpage::pluck('id')->toArray();

    return [

        'showpage_id' => $faker->randomElement($eventshowpage_pages),
    ];

});
