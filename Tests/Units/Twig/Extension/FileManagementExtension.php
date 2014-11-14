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
            ->if($testedClass = $this->createTestedClassInstance())
            ->given(
                $myEntity = $this->createEntityMock(array(
                    'nullPhotoFilename' => null,
                    'photoFilename' => '/my-photo.png',
                    'photoWithFormatFilename' => '/picture-{-imgformat-}.jpg',
                ))
            )
            ->then
                ->variable($testedClass->imagepath($myEntity, 'nullPhoto', 'mini', false))
                    ->isNull()
                ->string($testedClass->imagepath($myEntity, 'photo', 'mini', false))
                    ->isEqualTo('/mydir/my-photo.png')
                ->string($testedClass->imagepath($myEntity, 'photoWithFormat', 'mini', false))
                    ->isEqualTo('/mydir/picture-mini.jpg')
                ->string($testedClass->imagepath($myEntity, 'photoWithFormat', 'normal', false))
                    ->isEqualTo('/mydir/picture-normal.jpg')
                ->string($testedClass->imagepath($myEntity, 'photo', 'mini'))
                    ->match('#^\/mydir\/my-photo.png\?v=\d+$#')
                ->exception(function() use ($testedClass, $myEntity) {
                    $testedClass->filepath($myEntity, 'undefinedField');
                })
                    ->isInstanceOf('Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException')
                    ->hasCode(0)
            ->given(
                $updatedAt = new \DateTime(),
                $updatedAt->setTimestamp(1408526830),
                $myEntity = $this->createEntityMock(array(
                    'nullPhotoFilename' => null,
                    'photoFilename' => '/my-photo.png',
                    'photoWithFormatFilename' => '/picture-{-imgformat-}.jpg',
                    'updatedAt' => $updatedAt,
                ))
            )
            ->then
                ->string($testedClass->imagepath($myEntity, 'photo', 'mini'))
                    ->isEqualTo('/mydir/my-photo.png?v=1408526830')
            ->given(
                $myEntity = $this->createEntityMock(array(
                    'nullPhotoFilename' => null,
                    'photoFilename' => '/my-photo.png',
                    'photoWithFormatFilename' => '/picture-{-imgformat-}.jpg',
                    'updatedAt' => null,
                ))
            )
            ->then
                ->string($testedClass->imagepath($myEntity, 'photo', 'mini'))
                    ->match('#^\/mydir\/my-photo.png\?v=\d+$#')
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

    public function testImageUrl()
    {
        $this
            ->if($testedClass = $this->createTestedClassInstance())
            ->given(
                $myEntity = $this->createEntityMock(array(
                        'nullPhotoFilename' => null,
                        'photoFilename' => '/my-photo.png',
                        'photoWithFormatFilename' => '/picture-{-imgformat-}.jpg',
                    ))
            )
            ->then
                ->variable($testedClass->imageUrl($myEntity, 'nullPhoto', 'mini', false))
                    ->isNull()
                ->string($testedClass->imageUrl($myEntity, 'photo', 'mini', false))
                    ->isEqualTo('http://localhost/mydir/my-photo.png')
                ->string($testedClass->imageUrl($myEntity, 'photoWithFormat', 'mini', false))
                    ->isEqualTo('http://localhost/mydir/picture-mini.jpg')
                ->string($testedClass->imageUrl($myEntity, 'photoWithFormat', 'normal', false))
                    ->isEqualTo('http://localhost/mydir/picture-normal.jpg')
                ->string($testedClass->imageUrl($myEntity, 'photo', 'mini'))
                    ->match('#^http:\/\/localhost\/mydir\/my-photo.png\?v=\d+$#')
                ->exception(function() use ($testedClass, $myEntity) {
                    $testedClass->imageUrl($myEntity, 'undefinedField', 'photo');
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
