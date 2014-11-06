<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy\Factory;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;
use Novaway\Bundle\FileManagementBundle\Strategy\CopyStrategy;
use Novaway\Bundle\FileManagementBundle\Strategy\UploadStrategy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Factory for strategy classes
 */
class StrategyFactory implements StrategyFactoryInterface
{
    /** @var string */
    private $rootPath;

    /** @var array */
    private $arrayFilepath;

    /**
     * {@inheritdoc}
     */
    public function __construct($rootPath, array $arrayFilepath)
    {
        $this->rootPath = $rootPath;
        $this->arrayFilepath = $arrayFilepath;
    }

    /**
     * {@inheritdoc}
     */
    public function create(BaseEntityWithFileInterface $entity, $propertyName)
    {
        $propertyGetter = $entity->getter($propertyName);
        $property = $entity->$propertyGetter();
        if (is_string($property)) {
            return new CopyStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName]);
        }

        return new UploadStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName]);
    }

    /**
     * {@inheritdoc}
     */
    public function createFromArrayPath($propertyName)
    {
        $strategyName = $this->getStrategyName($propertyName);
        switch ($strategyName) {
            case 'copy':
                return new CopyStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName]);

            case 'upload':
                return new UploadStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName]);
        }

        throw new \InvalidArgumentException(sprintf('Unknow strategy identified by name "%s".', $propertyName));
    }

    /**
     * Get strategy to user for property using files path definition
     *
     * To avoid BC in 2.* branch, $arrayFilepath allow usage of 1 dimension array (list of properties)
     * and 2 dimension array (list of properties with strategy association)
     * @TODO: remove 1 dimension support for 3.* branch
     *
     * @param string $propertyName
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getStrategyName($propertyName)
    {
        $property = $this->arrayFilepath[$propertyName];
        if (is_string($property)) {
            return 'upload';
        }

        if (isset($this->arrayFilepath[$propertyName]['strategy'])) {
            return $this->arrayFilepath[$propertyName]['strategy'];
        }

        throw new \InvalidArgumentException(sprintf('Unknow strategy for property "%s".', $propertyName));
    }
}
