<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Adapter;

use Novaway\Bundle\FileManagementBundle\Tests\Helper\FileTestCase;
use Novaway\Bundle\FileManagementBundle\Adapter;

class FilesystemAdapter extends FileTestCase
{
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
                ->boolean($testedClass->isFile($files))->isTrue()

            // with invalid file
            ->given(
                mkdir($basePath.'dir1')
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isFile($basePath.'dir1'))->isFalse()

            // with invalid file in traversable object
            ->given(
                $files = new \ArrayObject(array(
                    $basePath.'dir1', $basePath.'file1',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isFile($files))->isFalse()
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
                ->boolean($testedClass->isDirectory($directories))->isTrue()

            // with invalid directory
            ->given(
                touch($basePath.'file1')
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isDirectory($basePath.'file1'))->isFalse()

            // with invalid directory in traversable object
            ->given(
                $files = new \ArrayObject(array(
                    $basePath.'dir1', $basePath.'file1',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->boolean($testedClass->isDirectory($files))->isFalse()
        ;
    }

    private function createTestedClassInstance()
    {
        return new Adapter\FilesystemAdapter();
    }
} 
