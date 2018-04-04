<?php

namespace ORT\LaravelPackages\Image\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use ORT\LaravelPackages\Image\Models\Image;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ThumbnailCreatorCommand extends Command
{

    /** @var string */
    protected $signature = 'ort:images:thumnail:creator';

    /** @var string */
    protected $description = 'Thumbnail creator';

    /** @var array */
    protected $config = [];

    /**
     * @return int
     */
    public function handle(): int
    {
        $this->config = config('ortimageprovider');
        if (!is_dir($this->config['public']['path']['images'])) {
            mkdir($this->config['public']['path']['images'], 0777, true);
        }
        if (!is_dir($this->config['public']['path']['thumbnails'])) {
            mkdir($this->config['public']['path']['thumbnails'], 0777, true);
        }
        return $this->workOnImages();
    }

    /**
     * @return int
     */
    private function workOnImages(): int
    {
        $page = 1;
        do {
            /** @var LengthAwarePaginator|Collection|Image[] $items */
            $items = Image::paginate(null, ['*'], 'page', $page);
            if ($page === 1) {
                $this->output->progressStart($items->total());
            }
            foreach ($items as $item) {
                $this->output->progressAdvance();
                if (!file_exists($item->image_path)) {
                    $this->output->warning(sprintf('Source img not found: %s', $item->image_path));
                    continue;
                }
                $item->createThumbnails();
            }
            $page++;
        } while ($items->hasMorePages());
        $this->output->progressFinish();
        return 0;
    }

}
