<?php

namespace AppBundle\Service;

use League\Flysystem\FilesystemInterface;

class FileService
{
    /**
     * @var FilesystemInterface
     */
    private $fileSystem;

    public function __construct(FilesystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function fileSize($id)
    {
        $identifierParts = explode(':', $id);
        $product = $identifierParts[0];
        $work = $identifierParts[1];

        $file = vsprintf('/pdf/%s/%s/%s.pdf', [$product, $work, $work]);

        if ($this->fileSystem->has($file)) {
            return $this->fileSystem->getSize($file);
        }

        return 0;
    }
}
