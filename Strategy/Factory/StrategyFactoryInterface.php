<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy\Factory;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;

/**
 * Interface for strategies factory
 */
interface StrategyFactoryInterface
{
    /**
     * Create a strategy by detecting entity field type
     *
     * @param BaseEntityWithFileInterface $entity
     * @param string                      $propertyName
     * @return StrategyInterface
     */
    public function create(BaseEntityWithFileInterface $entity, $propertyName);

    /**
     * Create a strategy by search in configuration
     *
     * @param string $name
     * @return StrategyInterface
     */
    public function createFromArrayPath($propertyName);
} 
