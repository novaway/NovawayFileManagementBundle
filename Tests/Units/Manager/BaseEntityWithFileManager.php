<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Manager;

use Novaway\Bundle\FileManagementBundle\Tests\Helper\BaseManagerTestCase;
use Novaway\Bundle\FileManagementBundle\Manager;

class BaseEntityWithFileManager extends BaseManagerTestCase
{
    public function testManagerConstructorWithInvalidArgument()
    {
        $this
            ->given($entityManager = $this->createMockEntityManager())
            ->then
                ->exception(function() use ($entityManager) {
                    $testedClass = new Manager\BaseEntityWithFileManager(array(), $entityManager);
                })
                    ->isInstanceOf('\InvalidArgumentException')
        ;
    }

    /**
     * @dataProvider getFileAbsolutePathDataProvider
     */
    public function testGetFileAbsolutePath($filePaths, $property, $result)
    {
        $this
            ->given(
                $entity = $this->createMockEntity(array(
                    $property.'Filename' => 'my-photo.png',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance($filePaths))
            ->then
                ->string($testedClass->getFileAbsolutePath($entity, $property))
                    ->isEqualTo($result)
                ->exception(function() use ($testedClass, $entity) {
                    $testedClass->getFileWebPath($entity, 'undefinedField');
                })
                    ->isInstanceOf('\UnexpectedValueException')
        ;
    }

    /**
     * @dataProvider getFileWebPathDataProvider
     */
    public function testGetFileWebPath($filePaths, $property, $result)
    {
        $this
            ->given(
                $entity = $this->createMockEntity(array(
                    $property.'Filename' => 'my-photo.png',
                ))
            )
            ->if($testedClass = $this->createTestedClassInstance($filePaths))
            ->then
                ->string($testedClass->getFileWebPath($entity, $property))
                    ->isEqualTo($result)
                ->exception(function() use ($testedClass, $entity) {
                    $testedClass->getFileWebPath($entity, 'undefinedField');
                })
                    ->isInstanceOf('\UnexpectedValueException')
        ;
    }

    /**
     * @dataProvider getFilePropertiesDataProvider
     */
    public function testGetFileProperties($arrayFilePath, $result)
    {
        $this
            ->if($testedClass = $this->createTestedClassInstance($arrayFilePath, $this->createMockEntityManager()))
            ->then
                ->array($testedClass->getFileProperties())
                    ->containsValues($result)
                    ->size
                        ->isEqualTo(count($result))
        ;
    }

    public function testSave()
    {
        $this
            ->given(
                $entityManager = $this->createMockEntityManager(),
                $entity = $this->createMockEntity(array(
                    'userPhotoFilename' => 'my-photo.png',
                ))
            )
            ->if(
                $testedClass = $this->createTestedClassInstance(array(
                    'bundle.web' => '',
                    'userPhoto' => ''
                ), $entityManager)
            )
            ->then
                ->object($testedClass->save($entity))->isEqualTo($entity)
                ->mock($entityManager)
                    ->call('persist')
                        ->withArguments($entity)->once()
        ;
    }

    public function testDelete()
    {
        $this
            ->given(
                $entityManager = $this->createMockEntityManager(),
                $entity = $this->createMockEntity(array(
                    'userPhotoFilename' => 'my-photo.png',
                ))
            )
            ->if(
                $testedClass = $this->createTestedClassInstance(array(
                    'bundle.web' => '',
                    'userPhoto' => '',
                ), $entityManager)
            )
            ->then
                ->object($testedClass->delete($entity))->isEqualTo($entity)
                ->mock($entityManager)
                    ->call('remove')
                        ->withArguments($entity)->once()
        ;
    }

    public function testSaveWithFiles()
    {
        $this
            ->assert('BaseEntityWithFileManager::saveWithFiles common usage')
                ->given(
                    mkdir($this->workspace.'/upload'),
                    touch($this->workspace.'/my-file.txt'),
                    $entityManager = $this->createMockEntityManager(),
                    $entity = $this->createMockEntity(array(
                        'id' => null,
                        'userPhoto' => $this->createMockFileType($this->workspace.'/my-file.txt'),
                        'userPhotoFilename' => '',
                    ))
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/upload/',
                        'userPhoto' => '/{-origin-}.{-ext-}'
                    ), $entityManager)
                )
                ->when($result = $testedClass->saveWithFiles($entity))
                ->then
                    ->object($result)->isEqualTo($entity)
                    ->string($result->getUserPhotoFilename())->isEqualTo('/my-file.txt')
                    ->boolean(file_exists($this->workspace.'/upload/my-file.txt'))->isTrue()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->atLeastOnce()

            ->assert('BaseEntityWithFileManager::saveWithFiles with callback')
                ->given(
                    touch($this->workspace.'/my-file.txt'),
                    $callableMock = new \mock\MyCallable(),
                    $callableMock->getMockController()->myMethod = function() { },
                    $callableParams = array($callableMock, 'myMethod')
                )
                ->then
                    ->object($testedClass->saveWithFiles($entity, $callableParams))->isEqualTo($entity)
                    ->mock($callableMock)
                        ->call('myMethod')
                            ->withArguments($entity)->once()

            ->assert('BaseEntityWithFileManager::saveWithFiles with upload error')
                ->given(
                    $entity = $this->createMockEntity(array(
                        'id' => null,
                        'userPhoto' => $this->createMockFileType(),
                        'userPhotoFilename' => 'my-photo.png',
                    )),
                    $entity->getMockController()->getUserPhoto = function() { return null; },
                    $entityManager = $this->createMockEntityManager()
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/upload/',
                        'userPhoto' => '/{-origin-}.{-ext-}'
                    ), $entityManager)
                )
                ->then
                    ->object($testedClass->saveWithFiles($entity))->isEqualTo($entity)
                    ->mock($entity->userPhoto)
                        ->call('move')
                            ->never()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()
        ;
    }

    public function testDeleteWithFiles()
    {
        $this
            ->given(
                touch($this->workspace.'/my-file.txt'),
                $entityManager = $this->createMockEntityManager(),
                $entity = $this->createMockEntity(array(
                    'userPhoto' => $this->createMockFileType(),
                    'userPhotoFilename' => 'my-file.txt',
                ))
            )
            ->if(
                $testedClass = $this->createTestedClassInstance(array(
                    'bundle.web' => $this->workspace.'/',
                    'bundle.root' => $this->workspace.'/',
                    'userPhoto' => '/'
                ), $entityManager)
            )
            ->when($result = $testedClass->deleteWithFiles($entity))
            ->then
                ->object($result)->isEqualTo($entity)
                ->boolean(file_exists($this->workspace.'/my-file.txt'))->isFalse()
                ->mock($entityManager)
                    ->call('remove')
                        ->withArguments($entity)->once()
        ;
    }

    /**
     * @dataProvider slugDataProvider
     */
    public function testSlug($managerConfiguration, $str, $result)
    {
        $this
            ->if($testedClass = $this->createTestedClassInstance($managerConfiguration))
            ->then
                ->string($testedClass->slug($str))
                    ->isEqualTo($result)
        ;
    }

    public function testRemoveFiles()
    {
        $this
            ->assert('BaseEntityWithFileManager::removeFiles erase file and save entity')
                ->given(
                    touch($this->workspace.'/my-file.txt'),
                    $entityManager = $this->createMockEntityManager(),
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => '/my-file.txt',
                    ))
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/',
                    ), $entityManager)
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', true, true))
                ->then
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->boolean(file_exists($this->workspace.'/my-file.txt'))->isFalse()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()

            ->assert('BaseEntityWithFileManager::removeFiles save entity without erase file')
                ->given(touch($this->workspace.'/my-file.txt'))
                ->when($testedClass->removeFiles($entity, 'userPhoto', false, false))
                ->then
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->boolean(file_exists($this->workspace.'/my-file.txt'))->isTrue()
                    ->mock($entityManager)
                        ->call('persist')
                            ->never()

            ->assert('BaseEntityWithFileManager::removeFiles only erase file')
                ->given(
                    $entity = $this->createMockEntity(array(
                        'userPhotoFilename' => '/my-file.txt',
                    ))
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', true, false))
                ->then
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->boolean(file_exists($this->workspace.'/my-file.txt'))->isFalse()
                    ->mock($entityManager)
                        ->call('persist')
                            ->never()

            ->assert('BaseEntityWithFileManager::removeFiles only save entity')
                ->given(
                    touch($this->workspace.'/my-file.txt'),
                    $entity = $this->createMockEntity(array(
                        'userPhoto' => $this->createMockFileType(),
                        'userPhotoFilename' => 'my-photo.png',
                    ))
                )
                ->when($testedClass->removeFiles($entity, 'userPhoto', false, true))
                ->then
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->boolean(file_exists($this->workspace.'/my-file.txt'))->isTrue()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()

            ->assert('BaseEntityWithFileManager::removeFiles with array of properties')
                ->given(
                    touch($this->workspace.'/my-file.txt'),
                    touch($this->workspace.'/resume.pdf'),
                    $entity = $this->createMockEntity(array(
                        'userPhoto' => $this->createMockFileType(),
                        'userPhotoFilename' => '/my-file.txt',
                        'fileResume' => $this->createMockFileType(),
                        'fileResumeFilename' => '/resume.pdf',
                    ))
                )
                ->when($testedClass->removeFiles($entity, array('fileResume', 'userPhoto'), true, true))
                ->then
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->variable($entity->getFileResumeFilename())->isNull()
                    ->boolean(file_exists($this->workspace.'/my-file.txt'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/resume.pdf'))->isFalse()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()

            ->assert('BaseEntityWithFileManager::removeFiles with array of properties')
                ->given(
                    touch($this->workspace.'/my-file.txt'),
                    touch($this->workspace.'/resume.pdf'),
                    $entity = $this->createMockEntity(array(
                        'userPhoto' => $this->createMockFileType(),
                        'userPhotoFilename' => '/my-file.txt',
                        'fileResume' => $this->createMockFileType(),
                        'fileResumeFilename' => '/resume.pdf',
                    ))
                )
                ->if(
                    $testedClass = $this->createTestedClassInstance(array(
                        'bundle.web' => $this->workspace.'/',
                        'bundle.root' => $this->workspace.'/',
                        'userPhoto' => '/',
                        'fileResume' => '/',
                    ), $entityManager)
                )
                ->when($testedClass->removeFiles($entity, array(), true, true))
                ->then
                    ->variable($entity->getUserPhotoFilename())->isNull()
                    ->variable($entity->getFileResumeFilename())->isNull()
                    ->boolean(file_exists($this->workspace.'/my-file.txt'))->isFalse()
                    ->boolean(file_exists($this->workspace.'/resume.pdf'))->isFalse()
                    ->mock($entityManager)
                        ->call('persist')
                            ->withArguments($entity)->once()
        ;
    }

    public function testReplaceFile()
    {
        $this
            // common usage
            ->given(
                touch($this->workspace.'/source.jpg'),
                $entityManager = $this->createMockEntityManager(),
                $entity = $this->createMockEntity(array(
                    'userFile' => $this->createMockFileType(__DIR__.'/../../data/my-file.txt'),
                    'userFileFilename' => '/source.jpg'
                ))
            )
            ->if(
                $testedClass = $this->createTestedClassInstance(array(
                    'bundle.web' => $this->workspace.'/',
                    'bundle.root' => $this->workspace.'/',
                    'userFile' => '/{-origin-}.{-ext-}',
                ), $entityManager)
            )
            ->when($fileProperties = $testedClass->replaceFile($entity, 'userFile', __DIR__.'/../../data/my-file.txt'))
            ->then
                ->array($fileProperties)
                    ->hasKeys(array('extension', 'original', 'size', 'mime'))
                    ->string['extension']->isEqualTo('txt')
                    ->string['original']->isEqualTo('my-file.txt')
                    ->integer['size']->isGreaterThan(0)
                ->boolean(file_exists($this->workspace.'/source.jpg'))->isFalse()
                ->string($entity->getUserFileFilename())->isEqualTo('/my-file.txt')
                ->boolean(file_exists($this->workspace.'/my-file.txt'))->isTrue()

            // with destination
            ->given(
                touch($this->workspace.'/source-file.txt'),
                $entity = $this->createMockEntity(array(
                    'userFile' => $this->createMockFileType(__DIR__.'/../../data/my-file.txt'),
                    'userFileFilename' => '/source-file.txt',
                ))
            )
            ->when($fileProperties = $testedClass->replaceFile($entity, 'userFile', __DIR__.'/../../data/my-file.txt', '/dir1/dir2/replacement_file.txt'))
            ->then
                ->boolean(file_exists(__DIR__.'/../../data/my-file.txt'))->isTrue() // check for copy operation
                ->boolean(file_exists($this->workspace.'/my-photo_original.png'))->isFalse()
                ->string($entity->getUserFileFilename())->isEqualTo('/dir1/dir2/replacement_file.txt')
                ->boolean(file_exists($this->workspace.'/dir1/dir2/replacement_file.txt'))->isTrue()

            // with rename operation
            ->given(
                mkdir($this->workspace.'/temp'),
                touch($this->workspace.'/temp/uploaded-file.txt')
            )
            ->when($testedClass->replaceFile($entity, 'userFile', $this->workspace.'/temp/uploaded-file.txt', null, Manager\BaseEntityWithFileManager::OPERATION_RENAME))
            ->then
                ->boolean(file_exists($this->workspace.'/uploaded-file.txt'))->isTrue()
                ->boolean(file_exists($this->workspace.'/temp/uploaded-file.txt'))->isFalse()
                ->string($entity->getUserFileFilename())->isEqualTo('/uploaded-file.txt')

            // with invalid file
            ->when($fileProperties = $testedClass->replaceFile($entity, 'userFile', $this->workspace.'/invalid-file.txt'))
            ->then
                ->variable($fileProperties)->isNull()
        ;
    }

    public function getFileAbsolutePathDataProvider()
    {
        $managerDirPath = realpath(__DIR__.'/../../../Manager/Traits');

        return array(
            array(
                array('bundle.web' => '/bundle/dir/', 'bundle.root' => '/bundle/root/'),
                'userPhoto',
                '/bundle/root/my-photo.png',
            ),
            array(
                array('bundle.web' => '/bundle/dir/', 'userPhoto' => '/uploads/'),
                'userPhoto',
                $managerDirPath.'/../../../../../../../../web/bundle/dir/my-photo.png',
            ),
        );
    }

    public function getFileWebPathDataProvider()
    {
        return array(
            array(
                array('bundle.web' => '/bundle/web/', 'bundle.root' => '/bundle/root/'),
                'userPhoto',
                '/bundle/web/my-photo.png',
            ),
            array(
                array('bundle.web' => '/bundle/web/'),
                'userPhoto',
                '/bundle/web/my-photo.png',
            )
        );
    }

    public function getFilePropertiesDataProvider()
    {
        return array(
            array(
                array('bundle.web' => '', 'userPhoto' => ''),
                array('userPhoto')
            ),
            array(
                array('bundle.web' => '', 'bundle.root' => '', 'userPhoto' => ''),
                array('bundle.root', 'userPhoto')
            ),
            array(
                array('bundle.web' => '', 'bundle.root' => '', 'userPhoto' => '', 'userCertificate' => ''),
                array('bundle.root', 'userPhoto', 'userCertificate')
            ),
        );
    }

    public function slugDataProvider()
    {
        $configuration = array(
            'bundle.web' => '/bundle/web/',
            'bundle.root' => '/bundle/root/',
            'userPhoto' => '/uploads/',
        );

        return array(
            array($configuration, 'slug', 'slug'),
            array($configuration, 'test123', 'test123'),
            array($configuration, 'test*125@home', 'test-125-home'),
            array($configuration, 'TEST\\125#jk', 'test-125-jk'),
            array($configuration, 'my-slug+string', 'my-slug-string'),
        );
    }

    private function createTestedClassInstance(array $filepaths, $entityManager = null)
    {
        if (null === $entityManager) {
            $entityManager = $this->createMockEntityManager();
        }

        return new Manager\BaseEntityWithFileManager($filepaths, $entityManager);
    }
}
