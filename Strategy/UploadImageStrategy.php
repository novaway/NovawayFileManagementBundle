<?php

namespace Novaway\Bundle\FileManagementBundle\Strategy;

use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFileInterface;
use Novaway\Bundle\FileManagementBundle\Manager\ResizeManager;

class UploadImageStrategy extends UploadStrategy
{
    protected $imageFormatDefinition;
    protected $imageFormatChoices;

    /**
     * @param string       $rootPath
     * @param string       $propertyName
     * @param array|string $configuration
     */
    public function __construct($rootPath, $propertyName, $configuration, $imageFormatDefinition, $imageFormatChoices)
    {
        parent::__construct($rootPath, $propertyName, $configuration);

        $this->imageFormatDefinition = $imageFormatDefinition;
        $this->imageFormatChoices = $imageFormatChoices;
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
    protected function buildDestination(BaseEntityWithFileInterface $entity, $propertyName, $sourceFilepath = null, $format = null)
    {
        $destination = parent::buildDestination($entity, $propertyName, $sourceFilepath);
        if ($format === null) {
            return $destination;
        }

        return $this->transformPathWithFormat($destination, $format);
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
        if (!isset($this->imageFormatChoices[$propertyName])) {
            return parent::fileMove($entity, $propertyName, $fileDestination);
        }

        $propertyGetter = $entity->getter($propertyName);
        $propertySetter = $entity->setter($propertyName);

        // the file property can be empty if the field is not required
        if (null === $entity->$propertyGetter()) {
            return false;
        }

        $fileDestinationAbsolute = sprintf('%s%s', $this->rootPath, $fileDestination);
        if (preg_match('#(.+)/([^/.]+).([A-Z]{3,5})#i', $fileDestinationAbsolute, $destMatch)) {

            $tmpDir = sprintf('%s%s', $this->rootPath, 'tmp');
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
