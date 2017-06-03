<?php

$factory->define('App\Models\Participant', function (Faker\Generator $faker) {
    static $password;

    $participant_pages = App\Models\Page::wherehas('eventroles', function ($q) {
        $q->where('eventrole_id', 1);
    })
        ->pluck('id')->toArray();
    //->get()->random()->id;

    //dd($participants);

    return [
        'name'         => $faker->catchPhrase,
        'info'         => $faker->text($maxNbChars = 100),
        'private_info' => 'private_info',
        'page_id'      => $faker->optional()->randomElement($participant_pages),
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
