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
use Novaway\Bundle\FileManagementBundle\Tests\Helper\BaseManagerTestCase;

class ImageType extends BaseManagerTestCase{

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
                    ->withArguments(['format', 'update_cache', 'preview', 'web_directory'])
                    ->once()
                ->call('setDefaults')
                    ->withArguments([
                        'format'        => 'thumbnail',
                        'update_cache'  => true,
                        'preview'       => true,
                        'web_directory' => '/mydir/',
                    ])
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
                $testedClass->buildView($view, $form, ['preview' => false])
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
        $entityProperties = array(
            'pictureFilename' => 'my-photo-{-imgformat-}.png',
            'updatedAt' => $updatedAt,
        );

        $view = new \mock\Symfony\Component\Form\FormView();
        $form = $this->getMockForm('picture', $entityProperties);

        $this
            ->if(
                $testedClass = $this->createTestedClassInstance('/path/'),
                $testedClass->buildView($view, $form, [
                    'preview'       => true,
                    'format'        => $format,
                    'update_cache'  => $updateCache,
                    'web_directory' => '/mydir/',
                ])
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
        $updatedAt = new \DateTime();
        $updatedAt->setTimestamp(1408526830);

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
