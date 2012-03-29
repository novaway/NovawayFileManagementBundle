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
     * The entity manager used to persist and flush entities
     * Doctrine\ORM\EntityManager by default, but it can be replaced
     * (overwritting the save method might be required then)
     */
    protected $entityManager;

    /**
     * The manager constructor
     *
     * @param array  $arrayFilepath  Associative array containing the file
     *                               path for each property of the managed
     *                               entity. This array must also contain a
     *                               'root' and a 'web' path.
     * @param mixed  $entityManager  The entity manager used to persist
     *                               and save data.
     */
    public function __construct($arrayFilepath, $entityManager)
    {
        $this->arrayFilepath = $arrayFilepath;
        $this->entityManager = $entityManager;
    }

    /**
     * Build Getter string for a property
     *
     * @param   string  $propertyName   The property whose getter will bi return
     * @param   boolean $onlyFilename   Set to TRUE to return the property filename getter
     *                                  FALSE to return the getter for the property itself
     * @return  string  The getter method
     */
    private function getter($propertyName, $onlyFilename = false)
    {
        return sprintf('get%s%s',
            ucfirst($propertyName),
            $onlyFilename ? 'Filename' : '');
    }

    /**
     * Build Setter string for a property
     *
     * @param   string  $propertyName   The property whose setter will bi return
     * @param   boolean $onlyFilename   Set to TRUE to return the property filename setter
     *                                  FALSE to return the setter for the property itself
     * @return  string  The setter method
     */
    private function setter($propertyName, $onlyFilename = false)
    {
        return sprintf('set%s%s',
            ucfirst($propertyName),
            $onlyFilename ? 'Filename' : '');
    }

    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param  mixed   $entity        The current entity
     * @param  string  $propertyName  The property matching the file
     *
     * @return string  The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName)
    {
        $getter = $this->getter($propertyName, true);
        return sprintf('%s%s', $this->arrayFilepath['bundle.root'],
                $entity->$getter());
    }

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param  mixed   $entity        The current entity
     * @param  string  $propertyName  The property matching the file
     *
     * @return string  The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName)
    {
        $getter = $this->getter($propertyName, true);
        return sprintf('%s%s', $this->arrayFilepath['bundle.web'], $entity->$getter());
    }

    /**
     * Persist and flush the entity
     *
     * @param   BaseEntityWithFile $entity The entity to save
     *
     * @return  BaseEntityWithFile The saved entity
     */
    public function save(BaseEntityWithFile $entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * Saves an entity and manages its file storage
     *
     * @param   BaseEntityWithFile $entity The entity to save
     *
     * @return  BaseEntityWithFile The saved entity
     */
    public function saveWithFiles(BaseEntityWithFile $entity)
    {
        $managedProperties = $this->arrayFilepath;
        unset($managedProperties['bundle.root']);
        unset($managedProperties['bundle.web']);
        $managedProperties = array_keys($managedProperties);

        $entity = $this->save($entity);
        $fileAdded = false;
        foreach ($managedProperties as $propertyName) {
            $fileDestination = $this->prepareFileMove($entity, $propertyName);
            $fileAdded = $fileAdded || $this->fileMove($entity, $propertyName, $fileDestination);
        }

        if($fileAdded){
            $entity = $this->save($entity);
        }

        return $entity;
    }

    /**
     * Prepare the entity for file storage
     *
     * @param   BaseEntityWithFile  $entity         The entity owning the files
     * @param   string              $propertyName   The property linked to the file
     *
     * @return  string              The file destination name
     */
    protected function prepareFileMove(BaseEntityWithFile $entity, $propertyName)
    {
        $propertyGetter = $this->getter($propertyName);
        $propertyFileNameSetter = $this->setter($propertyName, true);

        if (null !== $entity->$propertyGetter() && $entity->$propertyGetter()->getError() === UPLOAD_ERR_OK) {

            $fileDestinationName = str_replace(
                array('{-ext-}', '{-origin-}'),
                array(
                $entity->$propertyGetter()->guessExtension(),
                $entity->$propertyGetter()->getClientOriginalName()
                ), $this->arrayFilepath[$propertyName]);

            $fileDestinationName = preg_replace(
                '#{([^}-]+)}#ie', '$entity->get("$1")', $fileDestinationName);

            $entity->$propertyFileNameSetter($fileDestinationName);

            return $fileDestinationName;
        }
    }

    /**
     * Move the file from temp upload to expected path.
     *
     * @param  BaseEntityWithFile   $entity             The entity associated to the file
     * @param  string               $propertyName       The property associated to the file
     * @param  string               $fileDestination    The relative directory where
     *                                                  the file will be stored
     * @return boolean              TRUE if file move successfully, FALSE otherwise
     */
    protected function fileMove(BaseEntityWithFile $entity, $propertyName, $fileDestination)
    {
        $propertyGetter = $this->getter($propertyName);
        $propertySetter = $this->setter($propertyName);

        // the file property can be empty if the field is not required
        if (null === $entity->$propertyGetter()) {
            return false;
        }

        if(preg_match(
            '#(.+)/([^/.]+).([A-Z]{3,5})#i',
            sprintf('%s%s', $this->arrayFilepath['bundle.root'], $fileDestination),
            $destMatch
            )
        ) {
            // move the file to the required directory
            $entity->$propertyGetter()->move(
                $destMatch[1],
                $destMatch[2].'.'.$destMatch[3]);

            // clean up the file property as you won't need it anymore
            $entity->$propertySetter(null);

            return true;
        }

        return false;
    }

    /**
     * Removes one or several file from the entity
     *
     * @param  BaseEntityWithFile $entity       The entity from witch the file will be removed
     * @param  mixed              $properties   A file property name or an array containing file property names
     * @param  boolean            $eraseFile    Set to False to keep file on the disk
     *
     * @return BaseEntityWithFile               The saved entity
     */
    public function removeFiles(BaseEntityWithFile $entity, $properties, $eraseFile = true)
    {
        if(!is_array($properties)) {
            if(is_string($properties)){
                $properties = array($properties);
            } else {
                throw new \InvalidArgumentException();
            }
        }

        foreach ($properties as $propertyName) {
            $path = $this->getFileAbsolutePath($entity, $propertyName);
            if ($path) {
                if($eraseFile){
                    unlink($path);
                }
                $setter = $this->setter($propertyName, true);
                $entity->$setter(null);
            }
        }

        $this->save($entity);
    }

}