<?php

$factory->define('App\Models\Show', function (Faker\Generator $faker) {

    return [
        'name'          => $faker->catchPhrase,
        'description'   => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'tagline'       => $faker->text($maxNbChars = 25),
        'slug'          => substr($faker->optional()->slug, 0, 60),
        'imageurl'      => $faker->imageUrl(200, 200, 'people'),
        'backgroundurl' => $faker->imageUrl(1400, 656, 'city'),
        'public'        => 1,
        'confirmed'     => 1,
    ];

});
