<?php

$factory->define('App\Models\EventParticipant', function (Faker\Generator $faker) {
    static $password;

    $participant_pages = App\Models\Page::where('participant', 1)
        ->pluck('id')->toArray();
    //->get()->random()->id;

    //dd($participants);
    $imageurl = null;
    $page_id  = $faker->optional()->randomElement($participant_pages);

    if ($page_id) {
        $imageurl = $faker->imageUrl(200, 200, 'people');
    }

    return [
        'name'         => $faker->catchPhrase,
        'info'         => $faker->text($maxNbChars = 100),
        'imageurl'     => $imageurl,
        'private_info' => 'private_info',
        'page_id'      => $page_id,
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
