<?php

namespace Novaway\Bundle\FileManagementBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FileManagementExtension extends \Twig_Extension
{
    private $generator;
    private $webDirectory;
    private $accessor;

    public function __construct(UrlGeneratorInterface $generator, $webDirectory)
    {
        $this->generator    = $generator;
        $this->webDirectory = $webDirectory;
        $this->accessor     = PropertyAccess::createPropertyAccessor();
    }

    public function getFilters()
    {
        return array(
            'filepath'  => new \Twig_Filter_Method($this, 'filepath'),
            'imagepath' => new \Twig_Filter_Method($this, 'imagepath'),
            'fileUrl'   => new \Twig_Filter_Method($this, 'fileUrl'),
            'imageUrl'  => new \Twig_Filter_Method($this, 'imageUrl'),
        );
    }

    public function filepath($entity, $propertyName)
    {
        $property = $propertyName.'Filename';
        $filePath = $this->accessor->getValue($entity, $property);
        if (empty($filePath)) {
            return null;
        }

        return sprintf('%s/%s',
            rtrim($this->webDirectory, '/'),
            ltrim($filePath, '/')
        );
    }

    public function imagepath($entity, $propertyName, $format, $updateCache = true)
    {
        $pathTemplate = $this->filepath($entity, $propertyName);
        if (empty($pathTemplate)) {
            return null;
        }

        if ($updateCache) {
            $pathTemplate .= sprintf('?v=%s',
                $this->accessor->isReadable($entity, 'updatedAt') && $this->accessor->getValue($entity, 'updatedAt') instanceof \DateTime ?
                    $this->accessor->getValue($entity, 'updatedAt')->format('U') :
                    date('U')
            );
        }

        return str_replace('{-imgformat-}', $format, $pathTemplate);
    }

    public function fileUrl($entity, $propertyName)
    {
        $path = $this->filepath($entity, $propertyName);

        return $this->getUrl($path);
    }

    public function imageUrl($entity, $propertyName, $format, $updateCache = true)
    {
        $path = $this->imagepath($entity, $propertyName, $format, $updateCache);

        return $this->getUrl($path);
    }

    public function getName()
    {
        return 'twigextension';
    }

    private function getUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        return sprintf('%s://%s/%s',
            $this->generator->getContext()->getScheme(),
            rtrim($this->generator->getContext()->getHost(), '/'),
            ltrim($path, '/')
        );
    }
}

