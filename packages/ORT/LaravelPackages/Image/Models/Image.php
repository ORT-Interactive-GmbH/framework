<?php

namespace ORT\LaravelPackages\Image\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ORT\LaravelPackages\Image\Services\Image as ImageObject;

class Image extends Model
{

    /** @var array */
    protected $fillable = [
        'filename',
        'extension',
        'size',
        'width',
        'height',
        'colors',
        'exif',
        'tag',
        'model',
        'model_id',
        'model_type',
    ];

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'image_path',
        'url',
        'colors',
        'exif',
        'thumbnail',
        'image',
        'canon_exif',
        'file_exists'
    ];

    /**
     * @return string
     */
    public function getImagePathAttribute(): string
    {
        return sprintf(
            '%s/%d.%s',
            config('ortimageprovider.public.path.images'),
            $this->id,
            $this->extension ?? 'jpg'
        );
    }

    /**
     * @deprecated please use image_path attribute
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->getImagePathAttribute();
    }

    /**
     * @return bool
     */
    public function getFileExistsAttribute(): bool
    {
        return file_exists($this->image_path);
    }

    /**
     * @param string $index
     * @return string
     */
    public function getThumbnailPath(string $index): string
    {
        return sprintf(
            '%s/%d_%s.%s',
            config('ortimageprovider.public.path.thumbnails'),
            $this->id,
            $index,
            $this->extension
        );
    }

    /**
     * @return ImageObject|null
     */
    public function getImageAttribute(): ?ImageObject
    {
        if (file_exists($this->image_path)) {
            return ImageObject::createFromImage($this->image_path);
        }
        return null;
    }

    /**
     * @deprecated please use image attribute
     * @return ImageObject
     */
    public function getImage(): ImageObject
    {
        return $this->getImageAttribute();
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return asset_version(str_replace(
            public_path(),
            '',
            sprintf('%s/%d.jpg', config('ortimageprovider.public.path.images'), $this->id)
        ));
    }

    /**
     * @deprecated please use url attribute
     * @return string
     */
    public function getUrl(): string
    {
        return $this->getUrlAttribute();
    }

    /**
     * @param string $index
     * @return string
     */
    public function getThumbnailUrl(string $index = null): string
    {
        $sizes = config('ortimageprovider.thumbnail.sizes');
        if (!isset($sizes[$index])) {
            $index = array_pop(array_keys($sizes));
        }
        return asset_version(str_replace(
            public_path(),
            '',
            $this->getThumbnailPath($index)
        ));
    }

    /**
     * @return array
     */
    public function getThumbnailAttribute(): array
    {
        $result = [];
        foreach (config('ortimageprovider.thumbnail.sizes') as $name => $size) {
            $result[$name] = $this->getThumbnailUrl($name);
        }
        return $result;
    }

    /**
     * @return Image
     */
    public function createThumbnails(): self
    {
        $sizes = config('ortimageprovider.thumbnail.sizes');
        $image = $this->image;
        foreach ($sizes as $name => $size) {
            $hasAlpha = in_array($this->extension, ['png']);    // TODO more general solution to check if alpha channel may be relevant
            $image = $image->thumbnail($size['width'], $size['height'], $hasAlpha);
            $image->save($this->getThumbnailPath($name), 100, $this->extension);
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getColorsAttribute(): Collection
    {
        $data = $this->attributes['colors'] ?? '[]';
        $data = is_array($data) ? $data : json_decode($data, true);
        return collect($data);
    }

    /**
     * @return Collection
     */
    public function getExifAttribute(): Collection
    {
        $data = $this->attributes['exif'] ?? '[]';
        $data = is_array($data) ? $data : json_decode($data, true);
        return collect($data);
    }

    /**
     * @return null|Image
     */
    public function copy()
    {
        /** @var ImageObject $imageObject */
        try {
            $imageObject = image($this->getImagePathAttribute());
            /** @var Image $imageModel */
            $imageModel = Image::create($this->getAttributes());
            $imageObject->save($imageModel->image_path);
            $imageModel->createThumbnails();
            return $imageModel;
        } catch (\Exception $e) {
            \Log::warning(
                sprintf(
                    '%s[%d] %s in %s:%d',
                    get_class($e),
                    $e->getCode(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }
        return null;
    }

    /**
     * @return bool|null
     */
    public function delete()
    {
        @unlink($this->image_path);
        $sizes = config('ortimageprovider.thumbnail.sizes');
        foreach ($sizes as $name => $size) {
            $path = $this->getThumbnailPath($name);
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        return parent::delete();
    }

    /**
     * @see https://tools.ort-interactive.de/jira/browse/CAN-375
     * @see \App\Models\WorkshopImage::getCanonExifAttribute
     * @return string
     */
    public function getCanonExifAttribute(): string
    {
        $result = '';
//        foreach ($this->exif as $key => $value) {
//            if (strlen($value) > 0) {
//                $result .= sprintf('%s: %s<br>', trans('canon.' . $key), $value);
//            }
//        }
        return $result;
    }

}
