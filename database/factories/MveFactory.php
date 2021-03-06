<?php

$factory->define('App\Models\Mve', function (Faker\Generator $faker) {

    return [
        'name'          => $faker->company,
        'description'   => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),

        'imageurl'      => $faker->imageUrl(200, 200, 'nightlife'),
        'backgroundurl' => $faker->imageUrl(1400, 656, 'nightlife'),

    ];

});
