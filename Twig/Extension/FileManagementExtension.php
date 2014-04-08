<?php

namespace Novaway\Bundle\FileManagementBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FileManagementExtension extends \Twig_Extension
{
    private $generator;
    private $webDirectory;

    public function __construct(UrlGeneratorInterface $generator, $webDirectory)
    {
        $this->generator = $generator;
        $this->webDirectory = $webDirectory;
    }

    public function getFilters()
    {
        return array(
            'filepath' => new \Twig_Filter_Method($this, 'filepath'),
            'imagepath' => new \Twig_Filter_Method($this, 'imagepath'),
            'fileUrl' => new \Twig_Filter_Method($this, 'fileUrl'),
        );
    }

    public function filepath($entity, $propertyName)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $property = $propertyName.'Filename';
        $filePath = $accessor->getValue($entity, $property);

        return $this->webDirectory.$filePath;
    }

    public function imagepath($entity, $propertyName, $format)
    {
        $pathTemplate = $this->filepath($entity, $propertyName);

        return str_replace('{-imgformat-}', $format, $pathTemplate);
    }

    public function fileUrl($entity, $propertyName)
    {
        $path = $this->filepath($entity, $propertyName);

        return sprintf('%s://%s%s',
            $this->generator->getContext()->getScheme(),
            $this->generator->getContext()->getHost(),
            $path
        );
    }

    public function getName()
    {
        return 'twigextension';
    }

}
