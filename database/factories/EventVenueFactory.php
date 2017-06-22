<?php

$factory->define('App\Models\EventVenue', function (Faker\Generator $faker) {

    $eventvenues_venues = App\Models\Venue::pluck('id')->toArray();

    $lat = $faker->latitude($min = -90, $max = 90);
    $lng = $faker->longitude($min = -180, $max = 180);

    $start = $faker->dateTimeBetween('-3 days', '+7 days');

    $datetime  = $start;
    $precision = 30;

    // 1) Set number of seconds to 0 (by rounding up to the nearest minute if necessary)
    $second = (int) $datetime->format("s");
    if ($second > 30) {
        // Jumps to the next minute
        $datetime->add(new \DateInterval("PT" . (60 - $second) . "S"));
    } elseif ($second > 0) {
        // Back to 0 seconds on current minute
        $datetime->sub(new \DateInterval("PT" . $second . "S"));
    }
    // 2) Get minute
    $minute = (int) $datetime->format("i");
    // 3) Convert modulo $precision
    $minute = $minute % $precision;
    if ($minute > 0) {
        // 4) Count minutes to next $precision-multiple minutes
        $diff = $precision - $minute;
        // 5) Add the difference to the original date time
        $datetime->add(new \DateInterval("PT" . $diff . "M"));
    }

    $end        = clone $start;
    $eventhours = $faker->numberBetween(1, 6);
    $end->add(new DateInterval('PT' . $eventhours . 'H'));
    $end = $faker->optional()->randomElement($array = array($end));

    //dd($datetime, $start, $eventhours, $end);

    return [
        'venue_id'       => $faker->optional()->randomElement($eventvenues_venues),

        'venue_name'     => $faker->company,
        'street_address' => $faker->streetAddress,
        'city'           => $faker->city,
        'state'          => $faker->state,
        'postalcode'     => $faker->postcode,

        'lat'            => $lat,
        'lng'            => $lng,
        'venue_tagline'  => $faker->text($maxNbChars = 50),
        'start'          => $start,
        'end'            => $end,
        'location'       => DB::raw($lat . ', ' . $lng),
    ];

});
$factory->state('App\Models\Event', 'chicago', function ($faker) {
//Chicago
    $postalcodes = [60007, 60018, 60068, 60106, 60131, 60176, 60601, 60602, 60603, 60604, 60605, 60606, 60607, 60608, 60609, 60610, 60611, 60612, 60613, 60614, 60615, 60616, 60617, 60618, 60619, 60620, 60621, 60622, 60623, 60624, 60625, 60626, 60628, 60629, 60630, 60631, 60632, 60633, 60634, 60636, 60637, 60638, 60639, 60640, 60641, 60642, 60643, 60644, 60645, 60646, 60647, 60649, 60651, 60652, 60653, 60654, 60655, 60656, 60657, 60659, 60660, 60661, 60706, 60707, 60714, 60804, 60827];

    $lat = $faker->latitude($min = 41, $max = 42);
    $lng = $faker->longitude($min = -87.77, $max = -87.6);

    return [
        'city'       => 'Chicago',
        'state'      => 'IL',
        'postalcode' => $faker->randomElement($array = $postalcodes),
        'lat'        => $lat,
        'lng'        => $lng,
        'location'   => DB::raw($lat . ', ' . $lng),
    ];
});
