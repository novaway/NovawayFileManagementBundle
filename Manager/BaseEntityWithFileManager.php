<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;
use Novaway\Bundle\FileManagementBundle\Strategy\Factory\StrategyFactory;
use Novaway\Bundle\FileManagementBundle\Strategy\Factory\StrategyFactoryInterface;

/**
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithFileManager
{
    const OPERATION_COPY = 'copy';
    const OPERATION_RENAME = 'rename';

    /**
     * Stores the file path
     *
     * @var array $arrayFilepath
     */
    protected $arrayFilepath;

    /**
     * The absolute path to access files
     *
     * @var string $rootPath
     */
    protected $rootPath;

    /**
     * The relative path for web access to files
     *
     * @var string $webPath
     */
    protected $webPath;

    /**
     * The entity manager used to persist and flush entities
     * Doctrine\ORM\EntityManager by default, but it can be replaced
     * (overwritting the save method might be required then)
     *
     * @var mixed $entityManager
     */
    protected $entityManager;

    /**
     * Factory for strategy
     *
     * @var StrategyFactoryInterface
     */
    protected $strategyFactory;

    /**
     * The manager constructor
     *
     * @param array $arrayFilepath Associative array containing the file
     *                               path for each property of the managed
     *                               entity. This array must also contain a
     *                               'root' and a 'web' path.
     * @param mixed $entityManager The entity manager used to persist
     *                               and save data.
     * @param StrategyFactoryInterface $strategyFactory
     */
    public function __construct($arrayFilepath, $entityManager, StrategyFactoryInterface $strategyFactory = null)
    {
        if (!isset($arrayFilepath['bundle.web'])) {
            throw new \InvalidArgumentException('$arrayFilepath must have a bundle.web key (even empty).');
        }

        $this->entityManager = $entityManager;
        $this->webPath = $arrayFilepath['bundle.web'];

        unset($arrayFilepath['bundle.web']);
        if (isset($arrayFilepath['bundle.root']) && $arrayFilepath['bundle.root'] != null) {
            $this->rootPath = $arrayFilepath['bundle.root'];
            unset($arrayFilepath['bundle.root']);
        } else {
            $reflexionObject = new \ReflectionObject($this);
            $classDir        = dirname($reflexionObject->getFileName());
            $this->rootPath  = $classDir.'/../../../../../../../web'.$this->webPath;
        }
        $this->arrayFilepath = $arrayFilepath;

        $this->strategyFactory = $strategyFactory;
        if (null === $this->strategyFactory) {
            $this->strategyFactory = new StrategyFactory($this->rootPath, $this->arrayFilepath);
        }
    }

    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string             $propertyName The property matching the file
     *
     * @return string The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName)
    {
        $getter = $entity->getter($propertyName, true);

        try {
            $path = sprintf('%s%s', $this->rootPath, $entity->$getter());
        } catch (\Exception $e) {
            throw new \UnexpectedValueException();
        }

        return $path;
    }

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string             $propertyName The property matching the file
     *
     * @return string The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName)
    {
        $getter = $entity->getter($propertyName, true);

        try {
            $path = sprintf('%s%s', $this->webPath, $entity->$getter());
        } catch (\Exception $e) {
            throw new \UnexpectedValueException();
        }

        return $path;
    }

    /**
     * Returns all the document properties names for the managed entity
     *
     * @return array An array of the property names
     */
    public function getFileProperties()
    {
        return array_keys($this->arrayFilepath);
    }

    /**
     * Persist and flush the entity
     *
     * @param BaseEntityWithFile $entity The entity to save
     *
     * @return BaseEntityWithFile The saved entity
     */
    public function save(BaseEntityWithFile $entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * Remove and flush the entity
     *
     * @param BaseEntityWithFile $entity The entity to delete
     *
     * @return BaseEntityWithFile The deleted entity
     */
    public function delete(BaseEntityWithFile $entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * Saves an entity and manages its file storage
     *
     * @param BaseEntityWithFile $entity   The entity to save
     * @param callable|null      $callback A callback method. Ex : array(&$obj, 'somePublicMethod')
     *                                          The callback may have 3 parameters : original filename, extension, file size
     *
     * @return BaseEntityWithFile The saved entity
     */
    public function saveWithFiles(BaseEntityWithFile $entity, $callback = null)
    {
        $fileAdded = false;
        $callbackElementArray = array();
        $managedProperties = $this->getFileProperties();

        $entity = $this->save($entity);

        foreach ($managedProperties as $propertyName) {
            $strategy = $this->strategyFactory->create($entity, $propertyName);
            $strategy->process($entity);

            $callbackElementArray[$propertyName] = $strategy->getFileProperties();
        }

        if (is_callable($callback)) {
            call_user_func($callback, $entity, $callbackElementArray);
        }

        if ($fileAdded) {
            $entity = $this->save($entity);
        }

        return $entity;
    }

    /**
     * Deletes an entity and manages its file storage
     *
     * @param BaseEntityWithFile $entity The entity to delete
     *
     * @return BaseEntityWithFile The deleted entity
     */
    public function deleteWithFiles(BaseEntityWithFile $entity)
    {
        $managedProperties = $this->getFileProperties();
        $this->removeFiles($entity, $managedProperties, true, false);

        return $this->delete($entity);
    }

    /**
     * Builds the destination path for a file
     *
     * @param BaseEntityWithFile $entity         The entity of the file
     * @param string             $propertyName   The file property
     * @param string|null        $sourceFilePath The file source folder
     *
     * @return string The complete file path
     */
    protected function buildDestination(BaseEntityWithFile $entity, $propertyName, $sourceFilepath = null)
    {
        $propertyGetter = $entity->getter($propertyName);
        if (null === $sourceFilepath) {
            $sourceFilepath = $entity->$propertyGetter()->getClientOriginalName();
        }

        $arrReplacement =  array(
            '{-ext-}' => pathinfo($sourceFilepath, PATHINFO_EXTENSION),
            '{-origin-}' => pathinfo($sourceFilepath, PATHINFO_FILENAME)
        );

        if (method_exists($entity, 'getCustomPath')) {
            $arrReplacement['{-custom-}'] = $entity->getCustomPath($propertyName);
        }

        $fileDestinationName = str_replace(
            array_keys($arrReplacement),
            $arrReplacement,
            $this->arrayFilepath[$propertyName]);

        //Replace slugged placeholder
        $fileDestinationName = preg_replace_callback(
            '#{slug::([^}-]+)}#i',
            function ($matches) use ($entity) {
                $str = strtolower(trim($entity->get($matches[1])));
                $str = preg_replace('/[^a-z0-9-]/', '-', $str);
                return preg_replace('/-+/', "-", $str);
            },
            $fileDestinationName);

        //Replace date format placeholder
        $fileDestinationName = preg_replace_callback(
            '#{date::([^}-]+)::([^}-]+)}#i',
            function ($matches) use ($entity) {
                return $entity->get($matches[2])->format($matches[1]);
            },
            $fileDestinationName);

        //Replace classic placeholder
        $fileDestinationName = preg_replace_callback(
            '#{([^}-]+)}#i',
            function ($matches) use ($entity) {
                return $entity->get($matches[1]);
            },
            $fileDestinationName);

        return $fileDestinationName;
    }

    /**
     * Removes one or several file from the entity
     *
     * @param BaseEntityWithFile $entity       The entity from witch the file will be removed
     * @param array|string       $properties   A file property name or an array containing file property names
     * @param boolean            $doEraseFiles Set to FALSE to keep file on the disk
     * @param boolean            $doSave       Set to FALSE if you don't want to save the entity while file are deleted
     *
     * @return BaseEntityWithFile The saved entity
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
            $path = $this->getFileAbsolutePath($entity, $propertyName);
            if ($path) {
                if ($doEraseFiles && is_file($path)) {
                    unlink($path);
                }
                $setter = $entity->setter($propertyName, true);
                $entity->$setter(null);
            }
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
     * @param string             $sourceFilepath The file source folder
     * @param string|null        $destFilepath   The folder where the file will be copied
     * @param string             $operation      'copy' or 'rename'
     *
     * @return array|null An array containing informations about the copied file
     */
    public function replaceFile(BaseEntityWithFile $entity, $propertyName, $sourceFilepath, $destFilepath = null, $operation = self::OPERATION_COPY)
    {
        if (!in_array($operation, array(self::OPERATION_COPY, self::OPERATION_RENAME))) {
            throw new \InvalidArgumentException(sprintf('$operation only accept "%s" or "%s" value', self::OPERATION_COPY, self::OPERATION_RENAME));
        }

        if (!is_file($sourceFilepath)) {
            return null;
        }

        $propertyFileNameSetter = $entity->setter($propertyName, true);

        $oldDestPath = $this->getFileAbsolutePath($entity, $propertyName);
        if (is_file($oldDestPath)) {
            unlink($oldDestPath);
        }

        if (!$destFilepath) {
            $destFilepath = $this->buildDestination($entity, $propertyName, $sourceFilepath);
        }

        $fileInfo['extension'] = pathinfo($sourceFilepath, PATHINFO_EXTENSION);
        $fileInfo['original'] = pathinfo($sourceFilepath, PATHINFO_BASENAME);
        $fileInfo['size'] = filesize($sourceFilepath);
        $fileInfo['mime'] = mime_content_type($sourceFilepath);

        $entity->$propertyFileNameSetter($destFilepath);
        $absoluteDestFilepath = $this->getFileAbsolutePath($entity, $propertyName);
        $absoluteDestDir = substr($absoluteDestFilepath, 0, strrpos($absoluteDestFilepath, '/'));
        if (!is_dir($absoluteDestDir)) {
            mkdir($absoluteDestDir, 0777, true);
        }
        $operation($sourceFilepath, $absoluteDestFilepath);

        return $fileInfo;
    }

    /**
     * Get webpath
     *
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }
}

