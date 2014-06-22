<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Helper;

class BaseManagerTestCase extends FileTestCase
{
    protected function createMockFilesystem()
    {
        $filesystem = new \mock\Novaway\Bundle\FileManagementBundle\Adapter\FilesystemAdapter();
        $filesystem->getMockController()->chmod = function() { };

        return $filesystem;
    }

    protected function createMockEntityManager()
    {
        return new \mock\EntityManager();
    }

    protected function createMockFileType($originalFilename = null)
    {
        $controller = null;
        if (null === $originalFilename) {
            $controller = new \atoum\mock\controller();
            $controller->__construct = function() { };
        }

        $filetype = new \mock\Symfony\Component\HttpFoundation\File\UploadedFile(
            $originalFilename,
            basename($originalFilename),
            null,
            null,
            null,
            true,
            $controller
        );

        if (null !== $originalFilename) {
            $filetype->getMockController()->getClientOriginalName = function() use ($originalFilename) {
                return $originalFilename;
            };
        }

        $filetype->getMockController()->getError = function() {
            return UPLOAD_ERR_OK;
        };

        return $filetype;
    }

    protected function createMockEntity(array $fields = array())
    {
        $entity = new \mock\Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile();
        foreach ($fields as $name => $value) {
            $entity->$name = $value;

            // mock accessors
            $accessor = sprintf('get%s', ucfirst($name));
            $entity->getMockController()->$accessor = function() use ($entity, $name) {
                return $entity->$name;
            };

            // mock setters
            $setter = sprintf('set%s', ucfirst($name));
            $entity->getMockController()->$setter = function($param) use ($entity, $name) {
                $entity->$name = $param;
                return $entity;
            };
        }

        return $entity;
    }
} 
