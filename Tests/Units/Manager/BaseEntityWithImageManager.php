<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Manager;

use Novaway\Bundle\FileManagementBundle\Tests\Helper\BaseManagerTestCase;
use Novaway\Bundle\FileManagementBundle\Manager;

class BaseEntityWithImageManager extends BaseManagerTestCase
{
    public function testManagerConstructorWithInvalidArgument()
    {
        $this
            ->given($entityManager = $this->createMockEntityManager())
            ->then
                ->exception(function() use ($entityManager) {
                    $testedClass = new Manager\BaseEntityWithImageManager(array(), $entityManager, array(), array());
                })
                    ->isInstanceOf('\InvalidArgumentException')
        ;
    }

    /**
     * @dataProvider getFileAbsolutePathDataProvider
     */
    public function testGetFileAbsolutePath($filePaths, $property, $format, $result)
    {
        $this
            ->given(
                $entity = $this->createMockEntity(array(
                    $property.'Filename' => 'my-photo_{-imgformat-}.png',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance($filePaths))
            ->then
                ->string($testedClass->getFileAbsolutePath($entity, $property, $format))
                    ->isEqualTo($result)
                ->exception(function() use ($testedClass, $entity) {
                    $testedClass->getFileWebPath($entity, 'undefinedField');
                })
                    ->isInstanceOf('\UnexpectedValueException')

        ;
    }

    public function testGetFileAbsolutePathWithInvalidFormat()
    {
        $filePaths = array(
            'bundle.web' => '/bundle/dir/',
            'bundle.root' => '/bundle/root/',
        );

        $this
            ->given(
                $entity = $this->createMockEntity(array(
                    'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance($filePaths))
            ->then
                ->exception(function() use ($testedClass, $entity) {
                    $testedClass->getFileAbsolutePath($entity, 'userPhoto', 'failDefinition');
                })
                    ->isInstanceOf('\InvalidArgumentException')
                    ->hasMessage('Unknow format : the format [failDefinition] isn\'t registered')

        ;
    }

    /**
     * @dataProvider getFileWebPathDataProvider
     */
    public function testGetFileWebPath($filePaths, $property, $format, $result)
    {
        $this
            ->given(
                $entity = $this->createMockEntity(array(
                    $property.'Filename' => 'my-photo_{-imgformat-}.png',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance($filePaths))
            ->then
                ->string($testedClass->getFileWebPath($entity, $property, $format))
                    ->isEqualTo($result)
                ->exception(function() use ($testedClass, $entity) {
                    $testedClass->getFileWebPath($entity, 'undefinedField');
                })
                    ->isInstanceOf('\UnexpectedValueException')
        ;
    }

    public function testRemoveFiles()
    {
        $this
            ->assert('BaseEntityWithImageManager::removeFile with file delete and entity save')
                ->given(
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    $entityManager = $this->createMockEntityManager(),
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ))
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/',
                        'secondFile' => '/',
                    ), $entityManager)
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', true, true))
                ->then
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()

            ->assert('BaseEntityWithImageManager::removeFile without file delete and entity save')
                ->given(
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ))
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', false, false))
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isTrue()
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->mock($entityManager)
                        ->call('persist')
                            ->never()

            ->assert('BaseEntityWithImageManager::removeFile without file delete')
                ->given(
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ))
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', false, true))
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isTrue()
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()

            ->assert('BaseEntityWithImageManager::removeFile without save entity')
                ->given(
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ))
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', true, false))
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->mock($entityManager)
                        ->call('persist')
                            ->never()

            ->assert('BaseEntityWithImageManager::removeFile with multiple properties')
                ->given(
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    touch($this->workspace.'/file_original.jpg'),
                    touch($this->workspace.'/file_format1Definition.jpg'),
                    touch($this->workspace.'/file_format2Definition.jpg'),
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                        'secondFileFilename' => 'file_{-imgformat-}.jpg',
                    ))
                )
                ->when($testedClass->removeFiles($entity, array('userPhoto', 'secondFile'), true, true))
                ->then
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/file_original.jpg'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/file_format1Definition.jpg'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/file_format2Definition.jpg'))->isTrue()
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->variable($entity->getSecondFileFilename())->isNull()

            ->assert('BaseEntityWithImageManager::removeFile with defaults parameters')
                ->given(
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    touch($this->workspace.'/file_original.jpg'),
                    touch($this->workspace.'/file_format1Definition.jpg'),
                    touch($this->workspace.'/file_format2Definition.jpg'),
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                        'secondFileFilename' => 'file_{-imgformat-}.jpg',
                    ))
                )
                ->when($testedClass->removeFiles($entity))
                ->then
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/file_original.jpg'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/file_format1Definition.jpg'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/file_format2Definition.jpg'))->isTrue()
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->variable($entity->getSecondFileFilename())->isNull()
        ;
    }

    public function testReplaceFile()
    {
        $this
            ->assert('BaseEntityWithImageManager::replaceFile common usage')
                ->given(
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    $entity = $this->createMockEntity(array(
                        'userPhoto' => $this->createMockFileType(__DIR__.'/../../data/image.png'),
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ),
                    $entityManager = $this->createMockEntityManager())
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/{-origin-}_{-imgformat-}.{-ext-}',
                    ), $entityManager)
                )
                ->when($fileProperties = $testedClass->replaceFile($entity, 'userPhoto', __DIR__.'/../../data/image.png'))
                ->then
                    ->array($fileProperties)
                        ->hasKeys(array('extension', 'original', 'size', 'mime'))
                        ->string['extension']->isEqualTo('png')
                        ->string['original']->isEqualTo('image.png')
                        ->integer['size']->isGreaterThan(0)
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->string($entity->getUserPhotoFilename())->isEqualTo('/image_{-imgformat-}.png')
                    ->boolean(file_exists($this->workspace.'/image_original.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/image_format1Definition.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/image_format2Definition.png'))->isTrue()

            ->assert('BaseEntityWithImageManager::replaceFile with destination file path')
                ->given(
                    mkdir($this->workspace.'/dir'),
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    $entity = $this->createMockEntity(array(
                        'userPhoto' => $this->createMockFileType(__DIR__.'/../../data/image.png'),
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ))
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/{-origin-}_{-imgformat-}.{-ext-}',
                    ), $entityManager)
                )
                ->when($testedClass->replaceFile($entity, 'userPhoto', __DIR__.'/../../data/image.png', '/dir1/dir2/replacement_{-imgformat-}.png'))
                ->then
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->string($entity->getUserPhotoFilename())->isEqualTo('/dir1/dir2/replacement_{-imgformat-}.png')
                    ->boolean(file_exists($this->workspace.'/dir1/dir2/replacement_original.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/dir1/dir2/replacement_format1Definition.png'))->isTrue()
                    ->boolean(file_exists($this->workspace.'/dir1/dir2/replacement_format2Definition.png'))->isTrue()

            ->assert('BaseEntityWithImageManager::replaceFile with $operation operation = "move"')
                ->given(
                    mkdir($this->workspace.'/dest'),
                    touch($this->workspace.'/my-photo_original.png'),
                    touch($this->workspace.'/my-photo_format1Definition.png'),
                    touch($this->workspace.'/my-photo_format2Definition.png'),
                    copy(__DIR__.'/../../data/image.png', $this->workspace.'/image.png'),
                    $entity = $this->createMockEntity(array(
                        'userPhoto' => $this->createMockFileType($this->workspace.'/image.png'),
                        'userPhotoFilename' => 'my-photo_{-imgformat-}.png',
                    ))
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/{-origin-}_{-imgformat-}.{-ext-}',
                    ), $entityManager)
                )
                ->when($testedClass->replaceFile($entity, 'userPhoto', $this->workspace.'/image.png', $this->workspace.'/dest/replacement_{-imgformat-}.png', Manager\BaseEntityWithImageManager::OPERATION_RENAME))
                ->then
                    ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format1Definition.png'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/my-photo_format2Definition.png'))->isFalse()
                    ->string($entity->getUserPhotoFilename())->isEqualTo($this->workspace.'/dest/replacement_{-imgformat-}.png')
                    ->boolean(file_exists($this->workspace.'/image.png'))->isFalse()

            ->assert('BaseEntityWithImageManager::replaceFile with invalid file')
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/{-origin-}_{-imgformat-}.{-ext-}',
                    ), $entityManager)
                )
                ->when($fileProperties = $testedClass->replaceFile($entity, 'userFile', $this->workspace.'/invalid-file.txt'))
                ->then
                    ->variable($fileProperties)->isNull()
        ;
    }

    public function getFileAbsolutePathDataProvider()
    {
        $managerDirPath = realpath(__DIR__.'/../../../Manager');
        $filePaths = array(
            'bundle.web' => '/bundle/dir/',
            'bundle.root' => '/bundle/root/',
        );
        $filePathsWithoutRoot = array(
            'bundle.web' => '/bundle/dir/',
        );

        return array(
            array($filePaths, 'userPhoto', null, '/bundle/root/my-photo_{-imgformat-}.png'),
            array($filePaths, 'userPhoto', 'original', '/bundle/root/my-photo_original.png'),
            array($filePaths, 'userPhoto', 'format1Definition', '/bundle/root/my-photo_format1Definition.png'),
            array($filePathsWithoutRoot, 'userPhoto', null, $managerDirPath.'/../../../../../../../web/bundle/dir/my-photo_{-imgformat-}.png'),
            array($filePathsWithoutRoot, 'userPhoto', 'original', $managerDirPath.'/../../../../../../../web/bundle/dir/my-photo_original.png'),
            array($filePathsWithoutRoot, 'userPhoto', 'format1Definition', $managerDirPath.'/../../../../../../../web/bundle/dir/my-photo_format1Definition.png'),
        );
    }

    public function getFileWebPathDataProvider()
    {
        $filePaths = array(
            'bundle.web' => '/bundle/web/',
            'bundle.root' => '/bundle/root/',
        );

        return array(
            array($filePaths, 'userPhoto', null, '/bundle/web/my-photo_{-imgformat-}.png'),
            array($filePaths, 'userPhoto', 'original', '/bundle/web/my-photo_original.png'),
            array($filePaths, 'userPhoto', 'format1Definition', '/bundle/web/my-photo_format1Definition.png'),
        );
    }

    private function createTestedClassInstance(array $filepaths, $entityManager = null)
    {
        if (null === $entityManager) {
            $entityManager = $this->createMockEntityManager();
        }

        $imageFormatDefinitions = array(
            'format1Definition' => array('size' => 500, 'quality' => 95, 'bg_color' => 'FFFFFF'),
            'format2Definition' => array('crop' => false, 'quality' => 95, 'size' => 64),
        );
        $imageFormatChoices = array(
            'userPhoto' => array('original', 'format1Definition', 'format2Definition'),
            'secondFile' => array('format1Definition'),
        );

        return new Manager\BaseEntityWithImageManager($filepaths, $entityManager, $imageFormatDefinitions, $imageFormatChoices);
    }
}
