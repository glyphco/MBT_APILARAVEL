<?php

$factory->define('App\Models\Page', function (Faker\Generator $faker) {
    static $password;

    //    $lat = $faker->latitude($min = -90, $max = 90);
    //    $lng = $faker->longitude($min = -180, $max = 180);

//Chicago
    $lat = $faker->latitude($min = 41, $max = 42);
    $lng = $faker->longitude($min = -87.77, $max = -87.6);

    return [
        'name'           => $faker->catchPhrase,
        'email'          => $faker->safeEmail,
        'slug'           => substr($faker->optional()->slug, 0, 60),
        //'category'       => $faker->jobTitle,

        'street_address' => $faker->streetAddress,
        'city'           => $faker->city,
        'state'          => $faker->state,
        'postalcode'     => $faker->postcode,
        'lat'            => $lat,
        'lng'            => $lng,
        'phone'          => $faker->phoneNumber,
        'location'       => DB::raw($lat . ', ' . $lng),

        'imageurl'       => $faker->imageUrl(200, 200, 'people'),
        'backgroundurl'  => $faker->imageUrl(1400, 656, 'city'),

        'participant'    => 1,
        'production'     => 1,
        'canhavemembers' => 1,
        'canbeamember'   => 1,
        'public'         => 1,
        'confirmed'      => 1,

        //'speciality_id'  => 1,
        'tagline'        => $faker->text($maxNbChars = 25),
        'summary'        => $faker->text($maxNbChars = 140),

    ];

});

$factory->state('App\Models\Page', 'chicago', function ($faker) {
//Chicago
    $lat = $faker->latitude($min = 41, $max = 42);
    $lng = $faker->longitude($min = -87.77, $max = -87.6);

    return [
        'lat'      => $lat,
        'lng'      => $lng,
        'location' => DB::raw($lat . ', ' . $lng),
    ];
});
