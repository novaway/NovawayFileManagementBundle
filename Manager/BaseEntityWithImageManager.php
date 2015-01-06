<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Manager\Traits\ImageManagerTrait;

/**
 * Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager
 *
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithImageManager extends BaseEntityWithFileManager
{
    use ImageManagerTrait;

    /**
     * The manager constructor
     *
     * @param array $arrayFilepath         Associative array containing the file path for each property of the managed
     *                                     entity. This array must also contain a 'root' and a 'web' path.
     * @param mixed $entityManager         The entity manager used to persist and save data.
     * @param array $imageFormatDefinition Associative array to define image properties which be stored on filesystem
     * @param array $imageFormatChoices    Associative array to apply some format definitions to an entity property
     */
    public function __construct($arrayFilepath, $entityManager, $imageFormatDefinition, $imageFormatChoices)
    {
        $this->initialize($arrayFilepath, $imageFormatDefinition, $imageFormatChoices);

        $this->entityManager = $entityManager;
    }
}
