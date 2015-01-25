<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;
use Novaway\Bundle\FileManagementBundle\Manager\Traits\ImageManagerTrait;
use Novaway\Bundle\FileManagementBundle\Strategy\Factory\StrategyFactoryInterface;

/**
 * Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager
 *
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithImageManager implements FileManagerInterface
{
    use ImageManagerTrait;

    /**
     * The entity manager used to persist and flush entities
     * Doctrine\ORM\EntityManager by default, but it can be replaced
     * (overwritting the save method might be required then)
     *
     * @var mixed $entityManager
     */
    protected $entityManager;

    /**
     * The manager constructor
     *
     * @param array $arrayFilepath         Associative array containing the file path for each property of the managed
     *                                     entity. This array must also contain a 'root' and a 'web' path.
     * @param mixed $entityManager         The entity manager used to persist and save data.
     * @param array $imageFormatDefinition Associative array to define image properties which be stored on filesystem
     * @param array $imageFormatChoices    Associative array to apply some format definitions to an entity property
     */
    public function __construct($arrayFilepath, $entityManager, $imageFormatDefinition, $imageFormatChoices, StrategyFactoryInterface $strategyFactory = null)
    {
        $this->initialize($arrayFilepath, $imageFormatDefinition, $imageFormatChoices, $strategyFactory);

        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(BaseEntityWithFile $entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BaseEntityWithFile $entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $entity;
    }
}
