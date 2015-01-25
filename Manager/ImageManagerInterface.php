<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;

interface ImageManagerInterface extends FileManagerInterface
{
    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string             $propertyName The property matching the file
     * @param string|null        $format       The image format
     *
     * @return string The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName, $format = null);

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string        $propertyName The property matching the file
     * @param string|null        $format       The desired image format
     *
     * @return string The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName, $format = null);
}
