<?php

$factory->define('App\Models\EventShow', function (Faker\Generator $faker) {

    // $eventshows = App\Models\Show::pluck('id')->toArray();

    // return [

    //     'show_id' => $faker->randomElement($eventshows),
    // ];

    $randomShow = App\Models\Show::get()->random();

    return [
        'show_id'  => $randomShow->id,
        'name'     => $randomShow->name,
        'info'     => $randomShow->tagline,
        'imageurl' => $randomShow->imageurl,
    ];

});
