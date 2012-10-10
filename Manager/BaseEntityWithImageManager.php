<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use Symfony\Component\HttpFoundation\Request;
use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;

use PHPImageWorkshop\ImageWorkshop;


/**
 * Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager
 *
 * Extend your managers with this class to add File management.
 */
class BaseEntityWithImageManager extends BaseEntityWithFileManager
{
    private $imageFormatDefinition;

    private $imageFormatChoices;

    private $defaultConf;

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
    public function __construct($arrayFilepath, $entityManager, $imageFormatDefinition, $imageFormatChoices)
    {
     parent::__construct($arrayFilepath, $entityManager);
     $this->imageFormatDefinition = $imageFormatDefinition;
     $this->imageFormatChoices = $imageFormatChoices;
     $this->defaultConf = array(
        'fallback' => array(                // -- Default options when not overriden --
            'size' => 0,                    // Square size (set to 0 if not square)
            'width' => null,                // Width (if not square)
            'height' => null,               // Height (if not square)
            'crop' => false,                // Crop image
            'quality' => 75,                // Output image quality (from 0 to 100)
            'enlarge' => false,             // Enlarge image when source is smaller than output. Fill with bg_color when false
            'keep_proportions' => true,     // Keep source image proportions (and fill with blank if needed)
            'bg_color' => '#FFFFFF'),       // Background color when image does not fill expected output size
        'original' => array('quality' => 95),
        'thumbnail' => array('size' => 100, 'crop' => true),
        );
   }

   private function transformPathWithFormat($path, $format){
    return str_replace('{-imgformat-}', $format, $path);
}


    /**
     * Returns the absolute (root) filepath of a property for a specific entity
     *
     * @param  mixed   $entity        The current entity
     * @param  string  $propertyName  The property matching the file
     *
     * @return string  The absolute filepath
     */
    public function getFileAbsolutePath(BaseEntityWithFile $entity, $propertyName, $format = null)
    {
        if($format){
            return $this->transformPathWithFormat(
                parent::getFileAbsolutePath($entity, $propertyName),
                $format);
        } else {
            return parent::getFileAbsolutePath($entity, $propertyName);
        }
    }

    /**
     * Returns the relative (web) filepath of a property for a specific entity
     *
     * @param  mixed   $entity        The current entity
     * @param  string  $propertyName  The property matching the file
     *
     * @return string  The relative filepath
     */
    public function getFileWebPath(BaseEntityWithFile $entity, $propertyName, $format = null)
    {
        if($format){
            return $this->transformPathWithFormat(
                parent::getFileWebPath($entity, $propertyName),
                $format);
        } else {
            return parent::getFileWebPath($entity, $propertyName);
        }
    }

    /**
     * Builds the destination path for a file
     *
     * @param  BaseEntityWithFile $entity       The entity of the file
     * @param  string             $propertyName The file property
     * @param  string             $format       The image format
     *
     * @return string The complete file path
     */
    protected function buildDestination(BaseEntityWithFile $entity, $propertyName, $sourceFilepath = null, $format = null)
    {
        if($format){
            return $this->transformPathWithFormat(
                parent::buildDestination($entity, $propertyName, $sourceFilepath),
                $format);
        } else {
            return parent::buildDestination($entity, $propertyName, $sourceFilepath);
        }
    }

    /**
     * Move the file from temp upload to expected path.
     *
     * @param  BaseEntityWithFile   $entity             The entity associated to the file
     * @param  string               $propertyName       The property associated to the file
     * @param  string               $fileDestination    The relative directory where
     *                                                  the file will be stored
     * @param   array               $callbackElementArray   Values that will be used for callback
     *
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

        $fileDestinationAbsolute = sprintf('%s%s', $this->rootPath, $fileDestination);
        if(preg_match('#(.+)/([^/.]+).([A-Z]{3,5})#i', $fileDestinationAbsolute, $destMatch)) {

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
     * @param  string $sourcePath              The source image path
     * @param  string $fileDestinationAbsolute The destination path ({-img-format-} placeholder will be updated if neeeded)
     * @param  string $format                  The desired image format
     *
     * @return void
     */
    private function imageManipulation($sourcePath, $fileDestinationAbsolute, $format)
    {
        $layer = new ImageWorkshop(array('imageFromPath' => $sourcePath));
        $confPerso = isset($this->imageFormatDefinition[$format]) ? $this->imageFormatDefinition[$format] : null;
        $confDefault = isset($this->defaultConf[$format]) ? $this->defaultConf[$format] : null;
        $confFallback = $this->defaultConf['fallback'];
        $destPathWithFormat = $this->transformPathWithFormat($fileDestinationAbsolute, $format);
        $dim = array();

        foreach (array_keys($confFallback) as $key) {
            $dim[$key] = ($confPerso && isset($confPerso[$key])) ?
            $confPerso[$key] :
            (($confDefault && isset($confDefault[$key])) ? $confDefault[$key] : $confFallback[$key]);
        }

        if($dim['size'] > 0){
            if($dim['crop']){
                $layer->cropMaximumInPixel(0, 0, 'MM');
                $layer->resizeInPixel($dim['size'], $dim['size']);
            }
            else {
                $layer->resizeInPixel($dim['size'], $dim['size'], $dim['keep_proportions'], 0, 0, 'MM');
            }
        }
        elseif($dim['width'] != null || $dim['height'] != null) {
            //First case Scenario: Source image is smaller than expected output
            if ($layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height']) {

                if($dim['enlarge']){
                    $layer->resizeInPixel($dim['width'], $dim['height']);
                }
                else {
                    $boxLayer = new ImageWorkshop(array(
                        'width' => $dim['width'],
                        'height' => $dim['height'],
                        'backgroundColor' => $dim['bg_color'],
                    ));

                     //Stack source image on top of box layer (middle position)
                    $boxLayer->addLayerOnTop($layer, 0, 0, 'MM');
                    $layer = $boxLayer;
                }

            //Second case scenario: Source image is bigger than expected output
            } else{
                if($dim['crop']) {
                    $layer->cropMaximumInPixel(0, 0, 'MM');
                    $layer->resizeInPixel($dim['width'], $dim['height']);
                } else {
                    $layer->resizeInPixel($dim['width'], $dim['height'], $dim['keep_proportions'], 0, 0, 'MM');
                }

            }
        }


        $layer->save(
            substr($destPathWithFormat, 0, strrpos($destPathWithFormat, '/')),
            substr($destPathWithFormat, strrpos($destPathWithFormat, '/') + 1),
            true,
            null,
            $dim['quality']
            );

        $layer = null;
    }

    /**
     * Removes one or several file from the entity
     *
     * @param  BaseEntityWithFile $entity       The entity from witch the file will be removed
     * @param  mixed              $properties   A file property name or an array containing file property names
     * @param  boolean            $doEraseFiles Set to FALSE to keep file on the disk
     * @param  boolean            $doSave       Set to FALSE if you don't want to save the entity while file are deleted
     *
     * @return BaseEntityWithFile               The saved entity
     */
    public function removeFiles(BaseEntityWithFile $entity, $properties = array(), $doEraseFiles = true, $doSave = true)
    {
        parent::removeFiles($entity, $properties, $doEraseFiles, $doSave);
    }

    /**
     * Replace a property file by another, giver it's path
     *
     * @param BaseEntityWithFile $entity               The entity owning the files
     * @param string             $propertyName         The property linked to the file
     * @param array              $callbackElementArray Values that will be used for callback
     * @param string             $operation            'copy' or 'rename'
     *
     * @return array An array containing informations about the copied file
     */
    public function replaceFile(BaseEntityWithFile $entity, $propertyName, $sourceFilepath, $destFilepath = null, $operation = 'copy')
    {
        $propertyGetter = $this->getter($propertyName);
        $propertyFileNameGetter = $this->getter($propertyName, true);
        $propertyFileNameSetter = $this->setter($propertyName, true);

       if (is_file($sourceFilepath)) {

            if($destFilepath) {
                $entity->$propertyFileNameSetter($destFilepath);
            }
            else {
                $entity->$propertyFileNameSetter($this->buildDestination($entity, $propertyName, $sourceFilepath, null));
            }

            foreach ($this->imageFormatChoices[$propertyName] as $format) {

                $oldDestPath = $this->getFileAbsolutePath($entity, $propertyName, $format);
                if(is_file($oldDestPath)) {
                    unlink($oldDestPath);
                }

                $absoluteDestFilepath = $this->getFileAbsolutePath($entity, $propertyName, $format);
                $absoluteDestDir = substr($absoluteDestFilepath, 0, strrpos($absoluteDestFilepath, '/'));
                if(!is_dir($absoluteDestDir)){
                    mkdir($absoluteDestDir, 0777, true);
                }

                // $operation($sourceFilepath, $absoluteDestFilepath);
                $this->imageManipulation($sourceFilepath, $absoluteDestFilepath, $format);
            }

            $fileInfo['extension'] = pathinfo($sourceFilepath, PATHINFO_EXTENSION);
            $fileInfo['original'] = pathinfo($sourceFilepath, PATHINFO_BASENAME);
            $fileInfo['size'] = filesize($sourceFilepath);
            $fileInfo['mime'] = mime_content_type($sourceFilepath);

            return $fileInfo;
        }

        return null;
    }

}