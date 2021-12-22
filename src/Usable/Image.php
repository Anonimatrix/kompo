<?php

namespace Kompo;

use Kompo\Core\ImageHandler;
use Kompo\Elements\Traits\UploadsImages;

class Image extends File
{
    use UploadsImages;

    public $vueComponent = 'Image';

    /**
     * The file's handler class.
     */
    protected $fileHandler = ImageHandler::class;

    public function prepareForFront($komponent)
    {
        $this->value = $this->value ? $this->transformFromDB($this->value) : null;
    }
}
