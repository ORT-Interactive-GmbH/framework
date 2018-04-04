<?php

namespace ORT\LaravelPackages\Image\Services;

use Illuminate\Support\Collection;

class Image
{

    /** @var resource */
    private $image;

    /** @var int */
    private $width = 1;

    /** @var int */
    private $height = 1;

    /**
     * @param int $width
     * @param int $height
     */
    public function __construct(int $width = 1, int $height = 1)
    {
        $this->width = max(1, $width);
        $this->height = max(1, $height);
        $this->image = imagecreatetruecolor($this->width, $this->height);
    }

    /**
     * @param string $imagePath
     * @param string|null $type
     * @return Image
     */
    public static function createFromImage(string $imagePath, string $type = null): self
    {
        if ($type === null) {
            if (function_exists('exif_imagetype')) {
                if ($typeNumber = exif_imagetype($imagePath)) {
                    switch ($typeNumber) {
                        case IMAGETYPE_GIF:
                            $type = 'gif';
                            break;
                        case IMAGETYPE_JPEG:
                            $type = 'jpeg';
                            break;
                        case IMAGETYPE_PNG:
                            $type = 'png';
                            break;
                        case IMAGETYPE_WBMP:
                            $type = 'wbmp';
                            break;
                        case IMAGETYPE_XBM:
                            $type = 'xbm';
                            break;
                    }
                }
            }
            if ($type === null) {
                $info = pathinfo($imagePath);
                if (!isset($info['extension'])) {
                    throw new \RuntimeException('Could not found file type by ' . $imagePath);
                }
                $type = $info['extension'];
            }
        }
        $type = ucfirst(strtolower($type));
        if (!method_exists(static::class, 'createFrom' . $type)) {
            throw new \RuntimeException('Could not create Image from ' . $imagePath);
        }
        return call_user_func([static::class, 'createFrom' . $type], $imagePath);
    }

    /**
     * @alias createFromJpeg
     * @param string $imagePath
     * @return Image
     */
    public static function createFromJpg(string $imagePath): self
    {
        return static::createFromJpeg($imagePath);
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromJpeg(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromjpeg($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromPng(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefrompng($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromGif(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromgif($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromWbmp(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromwbmp($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromXbm(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromxbm($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromXpm(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromxpm($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromGd(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromgd($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @param string $imagePath
     * @return Image
     */
    public static function createFromGd2(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromgd2($imagePath);
        return $image->fixSizeInformation();
    }

    /**
     * @return Image
     */
    private function fixSizeInformation(): self
    {
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param string $imagePath
     * @return Collection
     */
    public static function getExifData(string $imagePath): Collection
    {
        $result = collect();
        if (file_exists($imagePath) && is_readable($imagePath) && $exif = @exif_read_data($imagePath)) {
            $result['model'] = $exif['Model'] ?? null; // Kamera Model
            $result['fnumber'] = $exif['FNumber'] ?? null; // Blendenanzahl
            $result['focallength'] = $exif['FocalLength'] ?? null; // Brennweite in mm
            $result['exposuretime'] = $exif['ExposureTime'] ?? null; // Belichtungszeit in Sek.
            $result['isospeedratings'] = $exif['ISOSpeedRatings'] ?? null; // ISO-Filmempfindlichkeit
            $result->filter(function ($value, $key) {
                return $value === null;
            });
        }
        return $result;
    }

    /**
     * @param int  $width
     * @param int  $height
     * @param bool $hasAlpha
     *
     * @return Image
     */
    public function resize(int $width, int $height, bool $hasAlpha = false): self
    {
        $image = new Image($width, $height);
        if ($hasAlpha) {
            imagealphablending($image->image, false);
        }

        imagecopyresized(
            $image->image, // dst
            $this->image, // src
            0, // dst x
            0, // dst y
            0, // src x
            0, // src y
            $image->width, // dst width
            $image->height, // dst height
            $this->width, // src width
            $this->height // src width
        );
        return $image;
    }

    /**
     * @param int  $maxWidth
     * @param int  $maxHeight
     * @param bool $hasAlpha
     *
     * @return Image
     */
    public function thumbnail(int $maxWidth, int $maxHeight, bool $hasAlpha = false): self
    {
        $width = $maxWidth;
        $height = $this->height / ($this->width / $maxWidth);
        if ($height > $maxHeight) {
            $width = $this->width / ($this->height / $maxHeight);
            $height = $maxHeight;
        }
        return $this->resize($width, $height, $hasAlpha);
    }

    /**
     * @param int $count
     * @param int $delta
     * @return Collection
     */
    public function getMainColors(int $count = 5, int $delta = 32): Collection
    {
        $count = max(1, $count);
        $delta = max(2, $delta);
        $halfDelta = $delta / 2;
        $hexColors = [];
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $index = imagecolorat($this->image, $x, $y);
                $colors = imagecolorsforindex($this->image, $index);
                $colors['red'] = intval((($colors['red']) + $halfDelta) / $delta) * $delta;
                $colors['green'] = intval((($colors['green']) + $halfDelta) / $delta) * $delta;
                $colors['blue'] = intval((($colors['blue']) + $halfDelta) / $delta) * $delta;
                $colors['red'] = max(0, min(255, $colors['red']));
                $colors['green'] = max(0, min(255, $colors['green']));
                $colors['blue'] = max(0, min(255, $colors['blue']));
                $hex = '#'
                    . substr('0' . dechex($colors['red']), -2)
                    . substr('0' . dechex($colors['green']), -2)
                    . substr('0' . dechex($colors['blue']), -2);
                $hexColors[$hex] = isset($hexColors[$hex]) ? $hexColors[$hex] + 1 : 1;
            }
        }
        arsort($hexColors, SORT_NUMERIC);
        return collect(array_slice(array_keys($hexColors), 0, $count));
    }

    /**
     * @return string
     */
    public function getMainColor(): string
    {
        return $this->getMainColors()[0];
    }

    /**
     * @param int    $quality
     * @param string $extension
     */
    public function toBrowser(int $quality = 100, string $extension = 'jpg'): void
    {
        // TODO find more generic solution to handle image types, maybe use IMAGETYPE_XXX constants
        if ($extension === 'png') {
            header('Content-Type: image/png');
            imagepng($this->image, null, $quality);
        } else {
            header('Content-Type: image/jpeg');
            imagejpeg($this->image, null, $quality);
        }

        exit(0);
    }

    /**
     * @param string $imagePath
     * @param int    $quality
     * @param string $extension
     *
     * @return Image
     */
    public function save(string $imagePath, int $quality = 100, ?string $extension = 'jpg'): self
    {
        $extension = $extension ?? 'jpg';
        $method = 'save' . ucfirst(strtolower($extension));
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $imagePath, $quality);
        }

        return $this->saveJpg($imagePath, $quality);
    }

    /**
     * @param string $path
     * @param int $quality
     *
     * @return Image
     */
    public function saveJpg(string $path, int $quality = 100): self
    {
        if (!imagejpeg($this->image, $path, $quality)) {
            throw new \RuntimeException('Could not store jpeg to ' . $path);
        }
        return $this;
    }

    /**
     * @param string $path
     * @param int    $quality
     *
     * @return Image
     */
    public function saveJpeg(string $path, int $quality = 100): self
    {
        return $this->saveJpg($path, $quality);
    }

    /**
     * @param string $path
     * @param int $quality
     *
     * @return Image
     */
    public function savePng(string $path, int $quality = 100): self
    {
        imagealphablending($this->image, true);
        imagesavealpha($this->image,true);
        if (!imagepng($this->image, $path, (int)($quality/100*9))) {
            throw new \RuntimeException('Could not store png to ' . $path);
        }
        return $this;
    }

}
