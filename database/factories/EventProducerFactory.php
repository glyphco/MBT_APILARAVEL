<?php

$factory->define('App\Models\EventProducer', function (Faker\Generator $faker) {

    $randomProducer = App\Models\Page::where('production', 1)->get()->random();

    return [
        'name'         => $randomProducer->name,
        'info'         => $randomProducer->tagline,
        'imageurl'     => $randomProducer->imageurl,
        'private_info' => 'private_info',
        'page_id'      => $faker->optional()->randomElement($array = [$randomProducer->id]),
        'public'       => 1,
        'confirmed'    => 1,
    ];

});
