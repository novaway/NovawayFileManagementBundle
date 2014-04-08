<?php

namespace Novaway\Bundle\FileManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageType extends AbstractType
{
    private $webDirectory;

    public function __construct($webDirectory)
    {
        $this->webDirectory = $webDirectory;
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'image';
    }

    /**
     * Add the image_path option
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('format'));
        $resolver->setOptional(array('preview'));
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['preview'] = array_key_exists('preview', $options) ? $options['preview'] : true;

        $view->vars['image_url'] = null;
        if($view->vars['preview'] && array_key_exists('format', $options))
        {
            $accessor = PropertyAccess::createPropertyAccessor();

            $property = $form->getName().'Filename';
            $parentData = $form->getParent()->getData();
            $imagePath = $accessor->getValue($parentData, $property);

            $view->vars['image_url'] = $imagePath ?
                str_replace('{-imgformat-}', $options['format'], $this->webDirectory.$imagePath) : null;
        }
    }
}