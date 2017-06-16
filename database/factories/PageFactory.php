<?php

$factory->define('App\Models\Page', function (Faker\Generator $faker) {
    static $password;

    return [
        'name'           => $faker->catchPhrase,
        'email'          => $faker->safeEmail,
        'slug'           => substr($faker->optional()->slug, 0, 60),
        //'category'       => $faker->jobTitle,

        'city'           => $faker->city,
        'state'          => $faker->state,
        'postalcode'     => $faker->postcode,
        'phone'          => $faker->phoneNumber,

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
    $postalcodes = [60007, 60018, 60068, 60106, 60131, 60176, 60601, 60602, 60603, 60604, 60605, 60606, 60607, 60608, 60609, 60610, 60611, 60612, 60613, 60614, 60615, 60616, 60617, 60618, 60619, 60620, 60621, 60622, 60623, 60624, 60625, 60626, 60628, 60629, 60630, 60631, 60632, 60633, 60634, 60636, 60637, 60638, 60639, 60640, 60641, 60642, 60643, 60644, 60645, 60646, 60647, 60649, 60651, 60652, 60653, 60654, 60655, 60656, 60657, 60659, 60660, 60661, 60706, 60707, 60714, 60804, 60827];

    return [
        'city'       => 'Chicago',
        'state'      => 'IL',
        'postalcode' => $faker->randomElement($array = $postalcodes),

    ];
});
