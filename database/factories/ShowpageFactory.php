<?php

$factory->define('App\Models\Showpage', function (Faker\Generator $faker) {
    static $password;

    //    $lat = $faker->latitude($min = -90, $max = 90);
    //    $lng = $faker->longitude($min = -180, $max = 180);

//Chicago
    $lat = $faker->latitude($min = 41, $max = 42);
    $lng = $faker->longitude($min = -87.77, $max = -87.6);

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
