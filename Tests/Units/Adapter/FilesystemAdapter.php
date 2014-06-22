<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Adapter;

use mageekguy\atoum;
use Novaway\Bundle\FileManagementBundle\Adapter;

class FilesystemAdapter extends atoum\test
{
    private $workspace = null;
    protected static $symlinkOnWindows = null;

    public function beforeTestMethod()
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

        $this->workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.time().rand(0, 1000);
        mkdir($this->workspace, 0777, true);
        $this->workspace = realpath($this->workspace);
    }

    public function afterTestMethod()
    {
        $this->clean($this->workspace);
    }

    public function testIsFile()
    {
        $this
            // common usage
            ->given(
                $basePath = $this->workspace.DIRECTORY_SEPARATOR,
                touch($basePath.'file1')
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isFile($basePath.'file1'))->isTrue()

            // with traversable object
            ->given(
                touch($basePath.'file2'),
                $files = new \ArrayObject(array(
                    $basePath.'file1', $basePath.'file2',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isFile($files))
                    ->isTrue()

            // with invalid file
            ->given(
                mkdir($basePath.'dir1')
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isFile($basePath.'dir1'))
                    ->isFalse()

            // with invalid file in traversable object
            ->given(
                $files = new \ArrayObject(array(
                    $basePath.'dir1', $basePath.'file1',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isFile($files))
                    ->isFalse()
        ;
    }

    public function testIsDirectory()
    {
        $this
            // common usage
            ->given(
                $basePath = $this->workspace.DIRECTORY_SEPARATOR,
                mkdir($basePath.'dir1')
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isDirectory($basePath.'dir1'))->isTrue()

            // with traversable object
            ->given(
                mkdir($basePath.'dir2'),

                $directories = new \ArrayObject(array(
                    $basePath.'dir1', $basePath.'dir1',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isDirectory($directories))
                    ->isTrue()

            // with invalid directory
            ->given(
                touch($basePath.'file1')
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isDirectory($basePath.'file1'))
                    ->isFalse()

            // with invalid directory in traversable object
            ->given(
                $files = new \ArrayObject(array(
                    $basePath.'dir1', $basePath.'file1',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isDirectory($files))
                    ->isFalse()
        ;
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

    private function createTestedClassInstance()
    {
        return new Adapter\FilesystemAdapter();
    }
} 
