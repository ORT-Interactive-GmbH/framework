<?php

namespace ORT\LaravelPackages\Image\Http\Controllers;

use App\Http\Controllers\Controller;
use ORT\LaravelPackages\Image\Models\Image;

class ThumbnailController extends Controller
{

    /**
     * @param Image $image
     * @param string $index
     * @return \Illuminate\Http\RedirectResponse
     */
    public function thumbnail(Image $image, string $index)
    {
        if ($image->file_exists) {
            $image->createThumbnails();
            $url = $image->getThumbnailUrl($index);
            return redirect()->to($url);
        }
        abort(404);
    }

}
