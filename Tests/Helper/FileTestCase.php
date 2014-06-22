<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Helper;

use mageekguy\atoum;

class FileTestCase extends atoum\test
{
    protected $workspace = null;
    protected static $symlinkOnWindows = null;

    public function beforeTestMethod($testMethod)
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            static::$symlinkOnWindows = true;
            $originDir = tempnam(sys_get_temp_dir(), 'sl');
            $targetDir = tempnam(sys_get_temp_dir(), 'sl');
            if (true !== @symlink($originDir, $targetDir)) {
                $report = error_get_last();
                if (is_array($report) && false !== strpos($report['message'], 'error code(1314)')) {
                    static::$symlinkOnWindows = false;
                }
            }
        }

        $this->workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.time().rand(0, 1000).uniqid();
        mkdir($this->workspace, 0777, true);
        $this->workspace = realpath($this->workspace);
    }

    public function afterTestMethod($testMethod)
    {
        $this->clean($this->workspace);
    }

    protected function clean($file)
    {
        if (is_dir($file) && !is_link($file)) {
            $dir = new \FilesystemIterator($file);
            foreach ($dir as $childFile) {
                $this->clean($childFile);
            }

            rmdir($file);
        } else {
            unlink($file);
        }
    }
} 
