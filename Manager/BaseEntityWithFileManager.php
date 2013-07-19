<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;
use Doctrine\ORM\EntityManager;

/**
 * Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager
 *
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithFileManager
{
    /**
     * Stores the file path
     *
     * array $arrayFilepath
     */
    protected $arrayFilepath;

    /**
     * The absolute path to access files
     *
     * string $rootPath
     */
    protected $rootPath;

    /**
     * The relative path for web access to files
     *
     * string $webPath
     */
    protected $webPath;

    /**
     * The entity manager used to persist and flush entities
     * Doctrine\ORM\EntityManager by default, but it can be replaced
     * (overwritting the save method might be required then)
     */
    protected $entityManager;

    /**
     * The manager constructor
     *
     * @param array $arrayFilepath Associative array containing the file
     *                               path for each property of the managed
     *                               entity. This array must also contain a
     *                               'root' and a 'web' path.
     * @param mixed $entityManager The entity manager used to persist
     *                               and save data.
     */
    public function __construct($arrayFilepath, $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->webPath = $arrayFilepath['bundle.web'];

        unset($arrayFilepath['bundle.web']);
        if (isset($arrayFilepath['bundle.root']) && $arrayFilepath['bundle.root'] != null) {
            $this->rootPath = $arrayFilepath['bundle.root'];
            unset($arrayFilepath['bundle.root']);
        } else {
            $this->rootPath = __DIR__.'/../../../../../../../web'.$this->webPath;
        }
        $this->arrayFilepath = $arrayFilepath;
    }

    /**
     * Build Getter string for a property
     *
     * @param string  $propertyName The property whose getter will bi return
     * @param boolean $filenameOnly Set to TRUE to return the property filename getter
     *                                  FALSE to return the getter for the property itself
     * @return string The getter method
     */
    protected function getter($propertyName, $filenameOnly = false)
    {
        return sprintf('get%s%s',
            ucfirst($propertyName),
            $filenameOnly ? 'Filename' : '');
    }

    /**
     * Build Setter string for a property
     *
     * @param string  $propertyName The property whose setter will bi return
     * @param boolean $filenameOnly Set to TRUE to return the property filename setter
     *                                  FALSE to return the setter for the property itself
     * @return string The setter method
     */
    protected function setter($propertyName, $filenameOnly = false)
    {
        return sprintf('set%s%s',
            ucfirst($propertyName),
            $filenameOnly ? 'Filename' : '');
    }

    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param mixed  $entity       The current entity
     * @param string $propertyName The property matching the file
     *
     * @return string The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName)
    {
        $getter = $this->getter($propertyName, true);
        $entity->$getter();
        $path = sprintf('%s%s', $this->rootPath,
            $entity->$getter());

        return $path;
    }

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param mixed  $entity       The current entity
     * @param string $propertyName The property matching the file
     *
     * @return string The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName)
    {
        $getter = $this->getter($propertyName, true);

        return sprintf('%s%s', $this->webPath, $entity->$getter());
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
     * @param array              $callback A callback method. Ex : array(&$obj, 'somePublicMethod')
     *                                          The callback may have 3 parameters : original filename, extension, file size
     *
     * @return BaseEntityWithFile The saved entity
     */
    public function saveWithFiles(BaseEntityWithFile $entity, $callback = null)
    {
        $managedProperties = $this->arrayFilepath;
        $managedProperties = array_keys($managedProperties);

        $entity = $this->save($entity);
        $fileAdded = false;
        $callbackElementArray = array();
        foreach ($managedProperties as $propertyName) {
            $fileDestination = $this->prepareFileMove($entity, $propertyName, $callbackElementArray);
            $fileAdded = $this->fileMove($entity, $propertyName, $fileDestination) || $fileAdded;
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
        $managedProperties = $this->arrayFilepath;
        $managedProperties = array_keys($managedProperties);

        $this->removeFiles($entity, $managedProperties, true, false);

        return $this->delete($entity);
    }

    /**
     * Builds the destination path for a file
     *
     * @param BaseEntityWithFile $entity       The entity of the file
     * @param string             $propertyName The file property
     *
     * @return string The complete file path
     */
    protected function buildDestination(BaseEntityWithFile $entity, $propertyName, $sourceFilepath = null)
    {
        $propertyGetter = $this->getter($propertyName);

        if ($sourceFilepath) {
            $arrReplacement =  array(
                    '{-ext-}' => pathinfo($sourceFilepath, PATHINFO_EXTENSION),
                    '{-origin-}' => pathinfo($sourceFilepath, PATHINFO_FILENAME)
                    );
        } else {
            $arrReplacement = array(
                    '{-ext-}'    => pathinfo($entity->$propertyGetter()->getClientOriginalName(), PATHINFO_EXTENSION),
                    '{-origin-}' => $this->slug(pathinfo($entity->$propertyGetter()->getClientOriginalName(), PATHINFO_FILENAME))
                    );
        }

        if (method_exists($entity, 'getCustomPath')) {
            $arrReplacement['{-custom-}'] = $entity->getCustomPath($propertyName);
        }

        $fileDestinationName = str_replace(
            array_keys($arrReplacement),
            $arrReplacement,
            $this->arrayFilepath[$propertyName]);

        //Replace slugged placeholder
        $fileDestinationName = preg_replace(
            '#{slug::([^}-]+)}#ie', '$this->slug($entity->get("$1"))', $fileDestinationName);
        //Replace date format placeholder
        $fileDestinationName = preg_replace(
            '#{date::([^}-]+)::([^}-]+)}#ie', '$entity->get("$2")->format("$1")', $fileDestinationName);
        //Replace classic placeholder
        $fileDestinationName = preg_replace(
            '#{([^}-]+)}#ie', '$entity->get("$1")', $fileDestinationName);

        return $fileDestinationName;
    }

    /**
     * Prepare the entity for file storage
     *
     * @param BaseEntityWithFile $entity               The entity owning the files
     * @param string             $propertyName         The property linked to the file
     * @param array              $callbackElementArray Values that will be used for callback
     *
     * @return string The file destination name
     */
    protected function prepareFileMove(BaseEntityWithFile $entity, $propertyName, &$callbackElementArray)
    {
        $propertyGetter = $this->getter($propertyName);
        $propertyFileNameGetter = $this->getter($propertyName, true);
        $propertyFileNameSetter = $this->setter($propertyName, true);

        if (null !== $entity->$propertyGetter() && $entity->$propertyGetter()->getError() === UPLOAD_ERR_OK) {

            $fileDestinationName = $this->buildDestination($entity, $propertyName);

            if (is_file($this->rootPath.$entity->$propertyFileNameGetter())) {
                unlink($this->rootPath.$entity->$propertyFileNameGetter());
            }
            $entity->$propertyFileNameSetter($fileDestinationName);

            $callbackElementArray[$propertyName]['extension'] = pathinfo($entity->$propertyGetter()->getClientOriginalName(), PATHINFO_EXTENSION);
            $callbackElementArray[$propertyName]['original'] = $entity->$propertyGetter()->getClientOriginalName();
            $callbackElementArray[$propertyName]['size'] = $entity->$propertyGetter()->getClientSize();
            $callbackElementArray[$propertyName]['mime'] = $entity->$propertyGetter()->getClientMimeType();

            return $fileDestinationName;
        }
    }

    /**
     * Creates a slug from a string
     *
     * @param string $str The string to slug
     *
     * @return string The slugged string
     */
    public function slug($str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);

        return $str;
    }

    /**
     * Move the file from temp upload to expected path.
     *
     * @param BaseEntityWithFile $entity          The entity associated to the file
     * @param string             $propertyName    The property associated to the file
     * @param string             $fileDestination The relative directory where
     *                                                  the file will be stored
     * @param array $callbackElementArray Values that will be used for callback
     *
     * @return boolean TRUE if file move successfully, FALSE otherwise
     */
    protected function fileMove(BaseEntityWithFile $entity, $propertyName, $fileDestination)
    {
        $propertyGetter = $this->getter($propertyName);
        $propertySetter = $this->setter($propertyName);

        // the file property can be empty if the field is not required
        if (null === $entity->$propertyGetter()) {
            return false;
        }

        $destFullPath = sprintf('%s%s', $this->rootPath, $fileDestination);
        if(preg_match(
            '#(.+)/([^/.]+).([A-Z]{3,5})#i',
            $destFullPath,
            $destMatch
            )
            ) {
            // move the file to the required directory
            $entity->$propertyGetter()->move(
                $destMatch[1],
                $destMatch[2].'.'.$destMatch[3]);

        chmod($destFullPath, 0755);

        // clean up the file property as you won't need it anymore
        $entity->$propertySetter(null);

        return true;
    }

    return false;
}

    /**
     * Removes one or several file from the entity
     *
     * @param BaseEntityWithFile $entity       The entity from witch the file will be removed
     * @param mixed              $properties   A file property name or an array containing file property names
     * @param boolean            $doEraseFiles Set to FALSE to keep file on the disk
     * @param boolean            $doSave       Set to FALSE if you don't want to save the entity while file are deleted
     *
     * @return BaseEntityWithFile The saved entity
     */
    public function removeFiles(BaseEntityWithFile $entity, $properties = array(), $doEraseFiles = true, $doSave = true)
    {
        if (!is_array($properties)) {
            if (is_string($properties)) {
                $properties = array($properties);
            } else {
                throw new \InvalidArgumentException();
            }
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
                $setter = $this->setter($propertyName, true);
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
     * @param [type]             $sourceFilepath [description]
     * @param [type]             $destFilepath   [description]
     * @param string             $operation      'copy' or 'rename'
     *
     * @return array An array containing informations about the copied file
     */
    public function replaceFile(BaseEntityWithFile $entity, $propertyName, $sourceFilepath, $destFilepath = null, $operation = 'copy')
    {
        $propertyGetter = $this->getter($propertyName);
        $propertyFileNameGetter = $this->getter($propertyName, true);
        $propertyFileNameSetter = $this->setter($propertyName, true);

        if (is_file($sourceFilepath)) {

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

        return null;
    }

}
