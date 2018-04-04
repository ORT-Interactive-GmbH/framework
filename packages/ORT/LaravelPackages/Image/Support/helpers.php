<?php

use ORT\LaravelPackages\Image\Services\Image;

if (!function_exists('image')) {
    /**
     * @param string $imagePath
     * @param string|null $type
     * @return Image
     */
    function image(string $imagePath, string $type = null): Image
    {
        return Image::createFromImage($imagePath, $type);
    }
}

if (!function_exists('thumbnail')) {
    /**
     * @param string $imagePath
     * @param int $width
     * @param int $height
     * @return Image
     */
    function thumbnail(string $imagePath, int $width, int $height): Image
    {
        return Image::createFromImage($imagePath)->thumbnail($width, $height);
    }
}

if (! function_exists('asset_version')) {
    /**
     * Generate an asset path for the application.
     *
     * @see vendor/laravel/framework/src/Illuminate/Foundation/helpers.php
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset_version($path, $secure = null)
    {
        static $cache;
        if (!isset($cache) || !is_array($cache)) {
            $cache = [];
        }
        if (!array_key_exists($path,$cache)) {
            $publicPath = public_path($path);
            $cache[$path] = File::exists($publicPath) ? File::lastModified($publicPath) : 0;
        }
        return sprintf('%s?_=%d',asset($path, $secure),$cache[$path]);
    }
}
