<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;

interface FileManagerInterface
{
    const OPERATION_COPY = 'copy';
    const OPERATION_RENAME = 'rename';

    /**
     * Persist and flush the entity
     *
     * @param BaseEntityWithFile $entity The entity to save
     *
     * @return BaseEntityWithFile The saved entity
     */
    public function save($entity);

    /**
     * Saves an entity and manages its file storage
     *
     * @param BaseEntityWithFile $entity   The entity to save
     * @param callable|null      $callback A callback method. Ex : array(&$obj, 'somePublicMethod')
     *                                     The callback may have 3 parameters : original filename, extension, file size
     *
     * @return BaseEntityWithFile The saved entity
     */
    public function saveWithFiles(BaseEntityWithFile $entity, $callback = null);

    /**
     * Remove and flush the entity
     *
     * @param BaseEntityWithFile $entity The entity to delete
     *
     * @return BaseEntityWithFile The deleted entity
     */
    public function delete($entity);

    /**
     * Deletes an entity and manages its file storage
     *
     * @param BaseEntityWithFile $entity The entity to delete
     *
     * @return BaseEntityWithFile The deleted entity
     */
    public function deleteWithFiles(BaseEntityWithFile $entity);

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
    public function replaceFile(BaseEntityWithFile $entity, $propertyName, $sourceFilepath, $destFilepath = null, $operation = self::OPERATION_COPY);

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
    public function removeFiles(BaseEntityWithFile $entity, $properties = array(), $doEraseFiles = true, $doSave = true);

    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string             $propertyName The property matching the file
     *
     * @return string The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName);

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string             $propertyName The property matching the file
     *
     * @return string The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName);

    /**
     * Returns all the document properties names for the managed entity
     *
     * @return array An array of the property names
     */
    public function getFileProperties();

    /**
     * Creates a slug from a string
     *
     * @param string $str The string to slug
     *
     * @return string The slugged string
     */
    public function slug($str);

    /**
     * Get webpath
     *
     * @return string
     */
    public function getWebPath();
}
