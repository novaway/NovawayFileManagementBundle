<?php

namespace Novaway\Bundle\FileManagementBundle\Manager\Traits;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;
use Novaway\Bundle\FileManagementBundle\Manager\ResizeManager;

trait ImageManagerTrait
{
    use FileManagerTrait {
        buildDestination as protected parentBuildDestination;
        fileMove as protected parentFileMove;
        getFileAbsolutePath as protected parentFileAbsolutePath;
        getFileWebPath as protected parentGetFileWebPath;
        initialize as protected parentInitialize;
        replaceFile as protected parentReplaceFile;
    }

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
     * Initialize trait properties
     *
     * @param array $arrayFilepath         Associative array containing the file path for each property of the managed
     *                                     entity. This array must also contain a 'root' and a 'web' path
     * @param array $imageFormatDefinition Associative array to define image properties which be stored on filesystem
     * @param array $imageFormatChoices    Associative array to apply some format definitions to an entity property
     */
    protected function initialize(array $arrayFilepath, $imageFormatDefinition, $imageFormatChoices)
    {
        $this->parentInitialize($arrayFilepath);

        $this->imageFormatDefinition = array_merge($imageFormatDefinition, array('original' => null));
        $this->imageFormatChoices = $imageFormatChoices;
    }

    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string             $propertyName The property matching the file
     * @param string|null        $format       The image format
     *
     * @return string The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName, $format = null)
    {
        if (!$format) {
            return $this->parentFileAbsolutePath($entity, $propertyName);
        }

        return $this->transformPathWithFormat($this->parentFileAbsolutePath($entity, $propertyName), $format);
    }

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param BaseEntityWithFile $entity       The current entity
     * @param string        $propertyName The property matching the file
     * @param string|null        $format       The desired image format
     *
     * @return string The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName, $format = null)
    {
        if (!$format) {
            return $this->parentGetFileWebPath($entity, $propertyName);
        }

        return $this->transformPathWithFormat($this->parentGetFileWebPath($entity, $propertyName), $format);
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
            foreach ($this->imageFormatChoices[$propertyName] as $format) {
                $path = $this->getFileAbsolutePath($entity, $propertyName, $format);
                if ($path) {
                    if ($doEraseFiles && is_file($path)) {
                        unlink($path);
                    }
                }
            }
            $setter = $this->setter($propertyName, true);
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
     * @param array              $formatList     Format to override, all if left null (recommended)
     *
     * @return array|null An array containing informations about the copied file
     */
    public function replaceFile(BaseEntityWithFile $entity, $propertyName, $sourceFilepath, $destFilepath = null, $operation = self::OPERATION_COPY, array $formatList = null)
    {
        if (!in_array($operation, array(self::OPERATION_COPY, self::OPERATION_RENAME))) {
            throw new \InvalidArgumentException(sprintf('$operation only accept "%s" or "%s" value', self::OPERATION_COPY, self::OPERATION_RENAME));
        }

        $propertyFileNameSetter = $this->setter($propertyName, true);

        if (is_file($sourceFilepath)) {

            $oldDestPathPattern = $this->getFileAbsolutePath($entity, $propertyName);
            if ($destFilepath) {
                $entity->$propertyFileNameSetter($destFilepath);
            } else {
                $entity->$propertyFileNameSetter($this->buildDestination($entity, $propertyName, $sourceFilepath, null));
            }

            if (null === $formatList) {
                $formatList = $this->imageFormatChoices[$propertyName];    
            }
            
            foreach ($formatList as $format) {

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
        if (!$format) {
            return $this->parentBuildDestination($entity, $propertyName, $sourceFilepath);
        }

        return $this->transformPathWithFormat($this->parentBuildDestination($entity, $propertyName, $sourceFilepath), $format);
    }

    /**
     * Move the file from temp upload to expected path.
     *
     * @param BaseEntityWithFile $entity          The entity associated to the file
     * @param string             $propertyName    The property associated to the file
     * @param string             $fileDestination The relative directory where the file will be stored
     *
     * @return boolean TRUE if file move successfully, FALSE otherwise
     */
    protected function fileMove(BaseEntityWithFile $entity, $propertyName, $fileDestination)
    {
        if (!isset($this->imageFormatChoices[$propertyName])) {
            return $this->parentFileMove($entity, $propertyName, $fileDestination);
        }

        $propertyGetter = $this->getter($propertyName);
        $propertySetter = $this->setter($propertyName);

        // the file property can be empty if the field is not required
        if (null === $entity->$propertyGetter()) {
            return false;
        }

        $fileDestinationAbsolute = sprintf('%s/%s',
            rtrim($this->rootPath, '/'),
            ltrim($fileDestination, '/')
        );

        if (preg_match('#(.+)/([^/.]+).([A-Z]{3,5})#i', $fileDestinationAbsolute, $destMatch)) {

            $tmpDir = sprintf('%s/%s',
                rtrim($this->rootPath, '/'),
                ltrim('tmp', '/')
            );
            $tmpName = uniqid().rand(0,999).'.'.$destMatch[3];
            $tmpPath = $tmpDir.'/'.$tmpName;

            $entity->$propertyGetter()->move($tmpDir,$tmpName);

            foreach ($this->imageFormatChoices[$propertyName] as $format) {
                $this->imageManipulation($tmpPath, $fileDestinationAbsolute, $format);
            }

            unlink($tmpPath);
            $entity->$propertySetter(null);

            return true;
        }

        return false;
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
}
