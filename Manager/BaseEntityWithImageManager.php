<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;
use Novaway\Bundle\FileManagementBundle\Strategy\Factory\StrategyFactoryInterface;
use Novaway\Bundle\FileManagementBundle\Strategy\Factory\StrategyImageFactory;

/**
 * Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager
 *
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithImageManager extends BaseEntityWithFileManager
{
    /**
     * Associative array to define image properties which be stored on filesystem
     *
     * @var array $imageFormatDefinitionAssociative
     */
    private $imageFormatDefinition;

    /**
     * Associative array to apply some format definitions to an entity property
     *
     * @var array $imageFormatChoices
     */
    private $imageFormatChoices;

    /**
     * The manager constructor
     *
     * @param array $arrayFilepath         Associative array containing the file path for each property of the managed
     *                                     entity. This array must also contain a 'root' and a 'web' path.
     * @param mixed $entityManager         The entity manager used to persist and save data.
     * @param array $imageFormatDefinition Associative array to define image properties which be stored on filesystem
     * @param array $imageFormatChoices    Associative array to apply some format definitions to an entity property
     * @param StrategyFactoryInterface $strategyFactory
     */
    public function __construct($arrayFilepath, $entityManager, $imageFormatDefinition, $imageFormatChoices, StrategyFactoryInterface $strategyFactory = null)
    {
        parent::__construct($arrayFilepath, $entityManager, $strategyFactory);

        $this->imageFormatDefinition = array_merge($imageFormatDefinition, array('original' => null));
        $this->imageFormatChoices = $imageFormatChoices;

        if (null === $strategyFactory) {
            $this->strategyFactory = new StrategyImageFactory($this->rootPath, $this->arrayFilepath, $this->imageFormatDefinition, $this->imageFormatChoices);
        }
    }

    /**
     * Transform a path string with format placeholder to the right path string
     *
     * @param  string $path   The path string with placeholder
     * @param  string $format The required format
     *
     * @return string The format path string
     */
    private function transformPathWithFormat($path, $format)
    {
        if (!array_key_exists($format, $this->imageFormatDefinition)) {
            throw new \InvalidArgumentException("Unknow format : the format [$format] isn't registered");
        }

        return str_replace('{-imgformat-}', $format, $path);
    }

    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string|null        $propertyName The property matching the file
     *
     * @return string The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName, $format = null)
    {
        $path = parent::getFileAbsolutePath($entity, $propertyName);
        if (null === $format) {
            return $path;
        }

        return $this->transformPathWithFormat($path, $format);
    }

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string|null        $propertyName The property matching the file
     * @param string|null        $format       The desired image format
     *
     * @return string The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName, $format = null)
    {
        $path = parent::getFileWebPath($entity, $propertyName);
        if (null === $format) {
            return $path;
        }

        return $this->transformPathWithFormat($path, $format);
    }

    /**
     * Builds the destination path for a file
     *
     * @param BaseEntityWithFile $entity         The entity of the file
     * @param string             $propertyName   The file property
     * @param string|null        $sourceFilepath The image source folder
     * @param string|null        $format         The desired image format
     *
     * @return string The complete file path
     */
    protected function buildDestination(BaseEntityWithFile $entity, $propertyName, $sourceFilepath = null, $format = null)
    {
        $destination = parent::buildDestination($entity, $propertyName, $sourceFilepath);
        if ($format === null) {
            return $destination;
        }

        return $this->transformPathWithFormat($destination, $format);
    }

    /**
     * Manipulates image according to image format definitons
     *
     * @param string $sourcePath              The source image path
     * @param string $fileDestinationAbsolute The destination path ({-img-format-} placeholder will be updated if neeeded)
     * @param string $format                  The desired image format
     */
    private function imageManipulation($sourcePath, $fileDestinationAbsolute, $format)
    {
        $destPathWithFormat = $this->transformPathWithFormat($fileDestinationAbsolute, $format);

        $resizeManager = new ResizeManager($this->imageFormatDefinition);
        $resizeManager->transform($sourcePath, $destPathWithFormat, $format);
    }

    /**
     * Removes one or several file from the entity
     *
     * @param BaseEntityWithFile $entity       The entity from witch the file will be removed
     * @param array|string       $properties   A file property name or an array containing file property names
     * @param boolean            $doEraseFiles Set to FALSE to keep file on the disk
     * @param boolean            $doSave       Set to FALSE if you don't want to save the entity while file are deleted
     */
    public function removeFiles(BaseEntityWithFile $entity, $properties = array(), $doEraseFiles = true, $doSave = true)
    {
        if (is_string($properties)) {
            $properties = array($properties);
        }

        if (!is_array($properties)) {
            throw new \InvalidArgumentException();
        }

        if (count($properties) == 0) {
            $properties = $this->getFileProperties();
        }

        foreach ($properties as $propertyName) {
            foreach ($this->imageFormatChoices[$propertyName] as $format) {
                $path = $this->getFileAbsolutePath($entity, $propertyName, $format);
                if ($path) {
                    if ($doEraseFiles && is_file($path)) {
                        unlink($path);
                    }
                }
            }
            $setter = $entity->setter($propertyName, true);
            $entity->$setter(null);
        }

        if ($doSave) {
            $this->save($entity);
        }
    }

    /**
     * Replace a property file by another, giver it's path
     *
     * @param BaseEntityWithFile $entity         The entity owning the files
     * @param string             $propertyName   The property linked to the file
     * @param string             $sourceFilepath The image source folder
     * @param string|null        $destFilepath   The image destination folder
     * @param string             $operation      'copy' or 'rename'
     *
     * @return array|null An array containing informations about the copied file
     */
    public function replaceFile(BaseEntityWithFile $entity, $propertyName, $sourceFilepath, $destFilepath = null, $operation = self::OPERATION_COPY)
    {
        if (!in_array($operation, array(self::OPERATION_COPY, self::OPERATION_RENAME))) {
            throw new \InvalidArgumentException(sprintf('$operation only accept "%s" or "%s" value', self::OPERATION_COPY, self::OPERATION_RENAME));
        }

        $propertyFileNameSetter = $entity->setter($propertyName, true);

        if (is_file($sourceFilepath)) {

            $oldDestPathPattern = $this->getFileAbsolutePath($entity, $propertyName);
            if ($destFilepath) {
                $entity->$propertyFileNameSetter($destFilepath);
            } else {
                $entity->$propertyFileNameSetter($this->buildDestination($entity, $propertyName, $sourceFilepath, null));
            }

            foreach ($this->imageFormatChoices[$propertyName] as $format) {

                $oldDestPath = $this->transformPathWithFormat($oldDestPathPattern, $format);
                if (is_file($oldDestPath)) {
                    unlink($oldDestPath);
                }

                $absoluteDestFilepath = $this->getFileAbsolutePath($entity, $propertyName, $format);
                $absoluteDestDir = substr($absoluteDestFilepath, 0, strrpos($absoluteDestFilepath, '/'));
                if (!is_dir($absoluteDestDir)) {
                    mkdir($absoluteDestDir, 0777, true);
                }

                $this->imageManipulation($sourceFilepath, $absoluteDestFilepath, $format);
            }

            $fileInfo['extension'] = pathinfo($sourceFilepath, PATHINFO_EXTENSION);
            $fileInfo['original'] = pathinfo($sourceFilepath, PATHINFO_BASENAME);
            $fileInfo['size'] = filesize($sourceFilepath);
            $fileInfo['mime'] = mime_content_type($sourceFilepath);

            // to simplfy image processing operation, this method works on uploaded file
            // in case developer decided to move uploaded file, we need to delete $sourceFilepath
            // after this process
            if (self::OPERATION_RENAME === $operation) {
                unlink($sourceFilepath);
            }

            return $fileInfo;
        }

        return null;
    }

    /**
     * Get image format choices
     *
     * @return array
     */
    public function getImageFormatChoices()
    {
        return $this->imageFormatChoices;
    }
}
