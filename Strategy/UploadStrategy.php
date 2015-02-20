<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;

/**
 * Manage uploaded file
 */
class UploadStrategy extends AbstractStrategy
{
    /**
     * {@inheritdoc}
     */
    public function __construct($rootPath, $propertyName, $configuration)
    {
        parent::__construct($rootPath, $propertyName, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function process(BaseEntityWithFileInterface $entity)
    {
        $fileDestination = $this->prepareFileMove($entity, $this->propertyName);
        return $this->fileMove($entity, $this->propertyName, $fileDestination);
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
    protected function prepareFileMove(BaseEntityWithFileInterface $entity, $propertyName)
    {
        $propertyGetter = $entity->getter($propertyName);
        $propertyFileNameGetter = $entity->getter($propertyName, true);
        $propertyFileNameSetter = $entity->setter($propertyName, true);

        if (null !== $entity->$propertyGetter() && $entity->$propertyGetter()->getError() === UPLOAD_ERR_OK) {

            $fileDestinationName = $this->buildDestination($entity, $propertyName);

            if (is_file($this->rootPath.$entity->$propertyFileNameGetter())) {
                unlink($this->rootPath.$entity->$propertyFileNameGetter());
            }
            $entity->$propertyFileNameSetter($fileDestinationName);

            $this->fileProcessed = array(
                'extension' => pathinfo($entity->$propertyGetter()->getClientOriginalName(), PATHINFO_EXTENSION),
                'original' => $entity->$propertyGetter()->getClientOriginalName(),
                'size' => $entity->$propertyGetter()->getClientSize(),
                'mime' => $entity->$propertyGetter()->getClientMimeType(),
            );

            return $fileDestinationName;
        }

        return null;
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
    protected function buildDestination(BaseEntityWithFileInterface $entity, $propertyName, $sourceFilepath = null, $format = null)
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

        $fileDestinationName = str_replace(array_keys($arrReplacement), $arrReplacement, $this->getPath());

        //Replace slugged placeholder
        $fileDestinationName = preg_replace_callback('#{slug::([^}-]+)}#i', function ($matches) use ($entity) {
            $str = $entity->get($matches[1]);
            $str = strtolower(trim($str));
            $str = preg_replace('/[^a-z0-9-]/', '-', $str);
            return preg_replace('/-+/', "-", $str);
        }, $fileDestinationName);

        //Replace date format placeholder
        $fileDestinationName = preg_replace_callback('#{date::([^}-]+)::([^}-]+)}#i', function ($matches) use ($entity) {
            return $entity->get($matches[2])->format($matches[1]);
        }, $fileDestinationName);

        //Replace classic placeholder
        $fileDestinationName = preg_replace_callback('#{([^}-]+)}#i', function ($matches) use ($entity) {
            return $entity->get($matches[1]);
        }, $fileDestinationName);

        return $fileDestinationName;
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
    protected function fileMove(BaseEntityWithFileInterface $entity, $propertyName, $fileDestination)
    {
        $propertyGetter = $entity->getter($propertyName);
        $propertySetter = $entity->setter($propertyName);

        // the file property can be empty if the field is not required
        if (null === $entity->$propertyGetter()) {
            return false;
        }

        $destFullPath = sprintf('%s%s', $this->rootPath, $fileDestination);
        if (preg_match('#(.+)/([^/.]+).([A-Z]{3,5})#i', $destFullPath, $destMatch)) {
            // move the file to the required directory
            $entity->$propertyGetter()->move($destMatch[1], $destMatch[2].'.'.$destMatch[3]);
            chmod($destFullPath, 0755);

            // clean up the file property as you won't need it anymore
            $entity->$propertySetter(null);

            return true;
        }

        return false;
    }
}
