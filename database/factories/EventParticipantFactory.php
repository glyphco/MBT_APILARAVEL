<?php

$factory->define('App\Models\EventParticipant', function (Faker\Generator $faker) {

    $randomParticipant = App\Models\Page::where('participant', 1)->get()->random();

    return [
        'name'         => $randomParticipant->name,
        'info'         => $randomParticipant->tagline,
        'imageurl'     => $randomParticipant->imageurl,
        'private_info' => 'private_info',
        'page_id'      => $faker->optional()->randomElement($array = [$randomParticipant->id]),
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
