<?php

$factory->define('App\Models\Event', function (Faker\Generator $faker) {

    $start = $faker->dateTimeBetween('-3 days', '+7 days');

    $eventhours = $faker->numberBetween(1, 6);

    $end = $faker->optional()->dateTimeBetween($start, $start->add(new DateInterval('PT' . $eventhours . 'H')));

    return [
        'name'        => $faker->company,
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),

        'start'       => $start,
        'end'         => $end,
        'public'      => 1,
        'confirmed'   => 1,
    ];

});
