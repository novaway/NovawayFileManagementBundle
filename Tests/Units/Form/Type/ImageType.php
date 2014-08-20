<?php
/**
 * Created by PhpStorm.
 * User: skwi
 * Date: 19/08/2014
 * Time: 14:25
 */

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Form\Type;

use mageekguy\atoum;
use Novaway\Bundle\FileManagementBundle\Form\Type;

class ImageType extends atoum\test {

    public function testSetDefaultOptions()
    {
        $this
            ->given(
                $resolver = new \mock\Symfony\Component\OptionsResolver\OptionsResolverInterface()
            )
            ->if(
                $testedClass = $this->createTestedClassInstance(),
                $testedClass->setDefaultOptions($resolver)
            )
            ->mock($resolver)
                ->call('setOptional')
                    ->withArguments(array('format', 'update_cache', 'preview'))
                    ->once()
                ->call('setDefaults')
                    ->withArguments(
                        array(
                            'format'       => 'thumbnail',
                            'update_cache' => true,
                            'preview'      => true
                        ))
                    ->once()
        ;
    }

    public function testBuildViewWithNoPreview()
    {
        $view = new \mock\Symfony\Component\Form\FormView();
        $form = $this->getMockForm('picture', array());

        $this
            ->if(
                $testedClass = $this->createTestedClassInstance(),
                $testedClass->buildView($view, $form, array('preview' => false))
            )
            ->array($view->vars)
                ->hasKey('image_url')
                ->hasKey('preview')
            ->boolean($view->vars['preview'])
                ->isFalse()
            ->variable($view->vars['image_url'])
                ->isNull()
        ;
    }

    /**
     * @dataProvider getBuildViewDataProvider
     */
    public function testBuildViewWithPreview($format, $updateCache, $updatedAt, $expectedUrlRegex)
    {
        $entityProperties = array('pictureFilename' => 'my-photo-{-imgformat-}.png');
        if ($updatedAt) {
           $entityProperties['updatedAt'] = $updatedAt;
        }
        $view = new \mock\Symfony\Component\Form\FormView();
        $form = $this->getMockForm('picture', $entityProperties);

        $this
            ->if(
                $testedClass = $this->createTestedClassInstance(),
                $testedClass->buildView($view, $form, array(
                    'preview'      => true,
                    'format'       => $format,
                    'update_cache' => $updateCache
                ))
            )
            ->array($view->vars)
                ->hasKey('image_url')
                ->hasKey('preview')
            ->boolean($view->vars['preview'])
                ->isTrue()
            ->string($view->vars['image_url'])
                ->match($expectedUrlRegex)
        ;
    }

    public function getBuildViewDataProvider()
    {
        $updatedAt = new \DateTime('2014-08-20 11:27:10');

        return array(
            //$format, $updateCache, $updatedAt, $expectedUrlRegex
            array('thumbnail', true, null, '#^\/mydir\/my-photo-thumbnail.png\?v=\d+$#'),
            array('original', false, null, '#^\/mydir\/my-photo-original.png$#'),
            array('thumbnail', true, $updatedAt, '#^\/mydir\/my-photo-thumbnail.png\?v=1408526830$#'),
        );
    }

    private function createTestedClassInstance($webdir = '/mydir/')
    {
        return new Type\ImageType($webdir);
    }

    private function createMockEntity(array $fields = array())
    {
        $entity = new \mock\Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile();
        foreach ($fields as $name => $value) {
            $entity->$name = $value;

            // mock accessors
            $accessor = sprintf('get%s', ucfirst($name));
            $entity->getMockController()->$accessor = function() use ($entity, $name) {
                return $entity->$name;
            };

            // mock setters
            $setter = sprintf('set%s', ucfirst($name));
            $entity->getMockController()->$setter = function($param) use ($entity, $name) {
                $entity->$name = $param;
                return $entity;
            };
        }

        return $entity;
    }

    private function getMockForm($name, $entityProperties)
    {
        $form = new \mock\Symfony\Component\Form\FormInterface();
        $form->getMockController()->getName = $name;

        $formParent = new \mock\Symfony\Component\Form\FormInterface();
        $formParent->getMockController()->getData = $this->createMockEntity($entityProperties);
        $form->getMockController()->getParent = $formParent;

        return $form;
    }
}
