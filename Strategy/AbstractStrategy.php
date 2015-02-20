<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy;

/**
 * Base strategy
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /** @var string */
    protected $rootPath;

    /** @var string */
    protected $propertyName;

    /** @var string|array */
    protected $configuration;

    /** @var array */
    protected $fileProcessed;

    /**
     * Constructor
     *
     * @param string       $rootPath
     * @param string       $propertyName
     * @param string|array $configuration
     */
    public function __construct($rootPath, $propertyName, $configuration)
    {
        $this->rootPath = $rootPath;
        $this->propertyName = $propertyName;
        $this->configuration = $configuration;
        $this->fileProcessed = null;
    }

    /**
     * Get file path for current managed field
     *
     * To avoid BC in 2.* branch, $arrayFilepath allow usage of 1 dimension array (list of properties)
     * and 2 dimension array (list of properties with strategy association)
     * @TODO: remove 1 dimension support for 3.* branch
     *
     * @return array|null|string
     */
    protected function getPath()
    {
        if (is_string($this->configuration)) {
            return $this->configuration;
        }

        if (isset($this->configuration['path'])) {
            return $this->configuration['path'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileProperties()
    {
        return $this->fileProcessed;
    }
} 
