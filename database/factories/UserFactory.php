<?php

$factory->defineAs('App\Models\User', 'user', function ($faker) {
	static $password;
	static $avatar;

	return [
		'name'           => $faker->name,
		'email'          => $faker->safeEmail,
		'avatar'         => $faker->imageUrl(100, 100, 'people'),
		//'password'       => $password ?: $password = bcrypt('secret'),
		'confirmed'      => 1,
		'is_banned'      => 0,
		'is_online'      => 0,
		'remember_token' => str_random(10),
	];

});
