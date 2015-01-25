<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;
use Novaway\Bundle\FileManagementBundle\Manager\Traits\FileManagerTrait;
use Novaway\Bundle\FileManagementBundle\Strategy\Factory\StrategyFactoryInterface;

/**
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithFileManager implements FileManagerInterface
{
    use FileManagerTrait;

    /**
     * The entity manager used to persist and flush entities
     * Doctrine\ORM\EntityManager by default, but it can be replaced
     * (overwritting the save method might be required then)
     *
     * @var mixed $entityManager
     */
    protected $entityManager;


    /**
     * Constructor
     *
     * @param array $arrayFilepath Associative array containing the file path for each property of the managed entity.
     *                             This array must also contain a 'root' and a 'web' path.
     * @param mixed $entityManager The entity manager used to persist and save data.
     */
    public function __construct($arrayFilepath, $entityManager, StrategyFactoryInterface $strategyFactory = null)
    {
        $this->initialize($arrayFilepath, $strategyFactory);

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
