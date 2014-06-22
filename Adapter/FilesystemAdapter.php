<?php

namespace Novaway\Bundle\FileManagementBundle\Adapter;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Filesystem Adapter for native PHP functions
 */
class FilesystemAdapter extends Filesystem
{
    /**
     * Checks if given parameter is file
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to check
     *
     * @return bool    true if the file exists, false otherwise
     */
    public function isFile($files)
    {
        foreach ($this->toIterator($files) as $file) {
            if (!is_file($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if given parameter is directory
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to check
     *
     * @return bool    true if the file exists, false otherwise
     */
    public function isDirectory($files)
    {
        foreach ($this->toIterator($files) as $file) {
            if (!is_dir($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $files
     *
     * @return \Traversable
     */
    private function toIterator($files)
    {
        if (!$files instanceof \Traversable) {
            $files = new \ArrayObject(is_array($files) ? $files : array($files));
        }

        return $files;
    }
} 
