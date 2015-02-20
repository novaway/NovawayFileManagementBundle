<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy\Factory;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;
use Novaway\Bundle\FileManagementBundle\Strategy\CopyImageStrategy;
use Novaway\Bundle\FileManagementBundle\Strategy\UploadImageStrategy;

/**
 * Factory for strategy classes
 */
class StrategyImageFactory implements StrategyFactoryInterface
{
    /** @var string */
    private $rootPath;

    /** @var array */
    private $arrayFilepath;

    private $imageFormatDefinition;
    private $imageFormatChoices;

    /**
     * {@inheritdoc}
     */
    public function __construct($rootPath, array $arrayFilepath, $imageFormatDefinition, $imageFormatChoices)
    {
        $this->rootPath = $rootPath;
        $this->arrayFilepath = $arrayFilepath;
        $this->imageFormatDefinition = $imageFormatDefinition;
        $this->imageFormatChoices = $imageFormatChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function create(BaseEntityWithFileInterface $entity, $propertyName)
    {
        if (null !== $entity->getPropertyPath($propertyName)) {
            return new CopyImageStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName], $this->imageFormatDefinition, $this->imageFormatChoices);
        }

        return new UploadImageStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName], $this->imageFormatDefinition, $this->imageFormatChoices);
    }

    /**
     * {@inheritdoc}
     */
    public function createFromArrayPath($propertyName)
    {
        $strategyName = $this->getStrategyName($propertyName);
        switch ($strategyName) {
            case 'copy':
                return new CopyImageStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName], $this->imageFormatDefinition, $this->imageFormatChoices);

            case 'upload':
                return new UploadImageStrategy($this->rootPath, $propertyName, $this->arrayFilepath[$propertyName], $this->imageFormatDefinition, $this->imageFormatChoices);
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
