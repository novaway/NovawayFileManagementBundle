<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Twig\Extension;

use mageekguy\atoum;
use Novaway\Bundle\FileManagementBundle\Twig\Extension;

class FileManagementExtension extends atoum\test
{
    public function testGetName()
    {
        $this
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->string($testedClass->getName())
                    ->isNotEmpty()
        ;
    }

    public function testGetFilters()
    {
        $this
            ->if($testedClass = $this->createTestedClassInstance())
            ->then
                ->array($testedClass->getFilters())
                    ->hasKey('filepath')
                    ->hasKey('imagepath')
                    ->hasKey('fileUrl')
        ;
    }

    public function testFilepath()
    {
        $this
            ->if(
                $testedClass = $this->createTestedClassInstance(),
                $myEntity = $this->createEntityMock(array(
                    'nullPhotoFilename' => null,
                    'photoFilename' => '/my-photo.png',
                ))
            )
            ->then
                ->variable($testedClass->filepath($myEntity, 'nullPhoto'))
                    ->isNull()
                ->string($testedClass->filepath($myEntity, 'photo'))
                    ->isEqualTo('/mydir/my-photo.png')
                ->exception(function() use ($testedClass, $myEntity) {
                    $testedClass->filepath($myEntity, 'undefinedField');
                })
                    ->isInstanceOf('Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException')
                    ->hasCode(0)
        ;
    }

    public function testImagepath()
    {
        $this
            ->if(
                $testedClass = $this->createTestedClassInstance(),
                $myEntity = $this->createEntityMock(array(
                    'nullPhotoFilename' => null,
                    'photoFilename' => '/my-photo.png',
                    'photoWithFormatFilename' => '/picture-{-imgformat-}.jpg',
                ))
            )
            ->then
                ->variable($testedClass->imagepath($myEntity, 'nullPhoto', 'mini'))
                    ->isNull()
                ->string($testedClass->imagepath($myEntity, 'photo', 'mini'))
                    ->isEqualTo('/mydir/my-photo.png')
                ->string($testedClass->imagepath($myEntity, 'photoWithFormat', 'mini'))
                    ->isEqualTo('/mydir/picture-mini.jpg')
                ->string($testedClass->imagepath($myEntity, 'photoWithFormat', 'normal'))
                    ->isEqualTo('/mydir/picture-normal.jpg')
                ->exception(function() use ($testedClass, $myEntity) {
                    $testedClass->filepath($myEntity, 'undefinedField');
                })
                    ->isInstanceOf('Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException')
                    ->hasCode(0)
        ;
    }

    public function testFileUrl()
    {
        $this
            ->if(
                $testedClass = $this->createTestedClassInstance(),
                $myEntity = $this->createEntityMock(array(
                    'nullPhotoFilename' => null,
                    'photoFilename' => '/my-photo.png',
                    'photoWithFormatFilename' => '/picture-{-imgformat-}.jpg',
                ))
            )
            ->then
                ->variable($testedClass->fileUrl($myEntity, 'nullPhoto', 'mini'))
                    ->isNull()
                ->string($testedClass->fileUrl($myEntity, 'photo', 'mini'))
                    ->isEqualTo('http://localhost/mydir/my-photo.png')
                ->string($testedClass->fileUrl($myEntity, 'photoWithFormat', 'mini'))
                    ->isEqualTo('http://localhost/mydir/picture-{-imgformat-}.jpg')
                ->string($testedClass->fileUrl($myEntity, 'photoWithFormat', 'normal'))
                    ->isEqualTo('http://localhost/mydir/picture-{-imgformat-}.jpg')
                ->exception(function() use ($testedClass, $myEntity) {
                    $testedClass->fileUrl($myEntity, 'undefinedField');
                })
                    ->isInstanceOf('Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException')
                    ->hasCode(0)
        ;
    }

    private function createTestedClassInstance($webdir = '/mydir')
    {
        $controller = new \mageekguy\atoum\mock\controller();
        $controller->__construct = function() {};

        $urlGenerator = new \mock\Symfony\Component\Routing\Generator\UrlGenerator(
            new \mock\Symfony\Component\Routing\RouteCollection(),
            new \mock\Symfony\Component\Routing\RequestContext(),
            null,
            $controller
        );

        return new Extension\FileManagementExtension($urlGenerator, $webdir);
    }

    private function createEntityMock(array $fields = array())
    {
        $myEntity = new \mock\MyEntity;
        foreach ($fields as $name => $value) {
            $myEntity->$name = $value;
        }

        return $myEntity;
    }
} 
