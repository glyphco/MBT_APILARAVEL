<?php

$factory->define('App\Models\EventCategory', function (Faker\Generator $faker) {
    static $password;

    $subcategories = App\Models\Subcategory::get()->random();

    return [
        'subcategory_id' => $subcategories->id,
        'category_id'    => $subcategories->category_id,
    ];

});
