<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;

interface StrategyInterface
{
    /**
     * Processing file using current strategy
     *
     * @param BaseEntityWithFileInterface $entity
     * @param string                      $propertyName
     * @return mixed
     */
    public function process(BaseEntityWithFileInterface $entity);

    /**
     * Return file information for latest processed file
     *
     * @return array|null
     */
    public function getFileProperties();
} 
