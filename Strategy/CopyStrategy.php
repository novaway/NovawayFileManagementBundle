<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;

/**
 * Manage file by coping it
 */
class CopyStrategy extends AbstractStrategy
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

    protected function prepareFileMove(BaseEntityWithFileInterface $entity, $propertyName)
    {
        $propertyGetter = $entity->getter($propertyName);
        $propertyFileNameGetter = $entity->getter($propertyName, true);
        $propertyFileNameSetter = $entity->setter($propertyName, true);

        $media = $entity->$propertyGetter();
        if (is_string($media)) {
            $fileDestinationName = $this->buildDestination($entity, $propertyName);

            if (is_file($this->rootPath.$entity->$propertyFileNameGetter())) {
                unlink($this->rootPath.$entity->$propertyFileNameGetter());
            }
            $entity->$propertyFileNameSetter($fileDestinationName);

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
    protected function buildDestination(BaseEntityWithFileInterface $entity, $propertyName, $sourceFilepath = null)
    {
        $propertyGetter = $entity->getter($propertyName);
        if (null === $sourceFilepath) {
            $sourceFilepath = $entity->$propertyGetter();
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

        // @TODO: potential risky
        if (false !== strpos($fileDestinationName, '?')) {
            $fileDestinationName = substr($fileDestinationName, 0, strpos($fileDestinationName, '?'));
        }

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
            if (false === @mkdir($destMatch[1], 0777, true)) {
                throw new \Exception(sprintf('Unable to create the "%s" directory', $destMatch[1]));
            }

            copy($entity->$propertyGetter(), $destFullPath);
            chmod($destFullPath, 0755);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->fileProcessed = array(
                'extension' => pathinfo($entity->$propertyGetter(), PATHINFO_EXTENSION),
                'original' => basename($entity->$propertyGetter()),
                'size' => filesize($destFullPath),
                'mime' => finfo_file($finfo, $destFullPath),
            );

            // clean up the file property as you won't need it anymore
            $entity->$propertySetter(null);

            return true;
        }

        return false;
    }
}
