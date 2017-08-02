<?php

$factory->define('App\Models\EventCategory', function (Faker\Generator $faker) {

    $subcategories = App\Models\Subcategory::get()->random();

    return [
        'category_id'      => $subcategories->category_id,
        'subcategory_id'   => $subcategories->id,
        'subcategory_name' => $subcategories->name,
    ];

});
