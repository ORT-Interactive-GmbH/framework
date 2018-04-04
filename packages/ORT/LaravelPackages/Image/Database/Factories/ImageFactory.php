<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\ORT\LaravelPackages\Image\Models\Image::class,
    function (Faker\Generator $faker) {
        return [
            'filename' => str_slug($faker->name) . '.jpg',
            'size' => mt_rand(1024, 2048),
            'width' => mt_rand(1080, 1920),
            'height' => mt_rand(1080, 1920),
            'colors' => collect(['#000000', '#333333', '#666666', '#999999', '#CCCCCC']),
            'exif' => collect([
                $faker->word => $faker->realText(mt_rand(10,255)),
                $faker->word => $faker->realText(mt_rand(10,255)),
                $faker->word => $faker->realText(mt_rand(10,255)),
                $faker->word => $faker->realText(mt_rand(10,255)),
                $faker->word => $faker->realText(mt_rand(10,255)),
            ])
        ];
    }
);
