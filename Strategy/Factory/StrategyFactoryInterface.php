<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy\Factory;

/**
 * Interface for strategies factory
 */
interface StrategyFactoryInterface
{
    /**
     * Constructor
     *
     * @param string $rootPath
     * @param array  $arrayFilepath
     */
    public function __construct($rootPath, array $arrayFilepath);

    /**
     * Create a strategy by name
     *
     * @param string $name
     * @return StrategyInterface
     */
    public function create($propertyName);
} 
