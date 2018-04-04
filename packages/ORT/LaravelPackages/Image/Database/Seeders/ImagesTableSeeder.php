<?php

namespace ORT\LaravelPackages\Image\Database\Seeders;

use Illuminate\Database\Seeder;
use ORT\LaravelPackages\Image\Models\Image;
use ORT\LaravelPackages\Image\Services\Image as ImageObject;

class ImagesTableSeeder extends Seeder
{

    public function run()
    {
        $path = __DIR__ . '/../../Examples';
        $images = glob($path . '/*.jpg');
        $counts = count($images) - 1;
        if (Image::count() < $counts) {
            foreach ($images as $imageSource) {
                factory(Image::class, 1)->create()->each(function (Image $image) use ($imageSource) {
                    copy($imageSource, $image->image_path);

                    $imageObject = $image->image;
                    $image->createThumbnails();
                    $image->update([
                        'filename' => basename($image->image_path),
                        'colors' => $imageObject->getMainColors(),
                        'width' => $imageObject->getWidth(),
                        'height' => $imageObject->getHeight(),
                        'size' => filesize($image->image_path),
                        'exif' => ImageObject::getExifData($image->image_path),
                    ]);
                });
            }
        }
    }

}
