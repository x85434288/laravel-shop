<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ProductSku::class, function (Faker $faker) {


    //$updated_at = $faker->dateTimeThisMonth();
    //$created_at = $faker->dateTimeThisMonth($updated_at);
    return [
        //
        'title' => $faker->word,
        'description' => $faker->sentence,
        'price'       => $faker->randomNumber(4),
        'stock'       => $faker->randomNumber(5),
       // 'created_at' => $created_at,
        //'updated_at' => $updated_at,
    ];

});
