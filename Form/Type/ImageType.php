<?php

namespace Novaway\Bundle\FileManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ImageType extends AbstractType
{
    /** @var string */
    private $webDirectory;

    /**
     * Constructor
     *
     * @param string $webDirectory
     */
    public function __construct($webDirectory)
    {
        $this->webDirectory = $webDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image';
    }

    /**
     * @deprecated The fonction is here to keep compatibility with Symfony < 2.6
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'format'        => 'thumbnail',
            'update_cache'  => true,
            'preview'       => true,
            'web_directory' => $this->webDirectory
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(['format', 'update_cache', 'preview', 'web_directory']);
        $resolver->setDefaults([
            'format'        => 'thumbnail',
            'update_cache'  => true,
            'preview'       => true,
            'web_directory' => $this->webDirectory
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['format', 'update_cache', 'preview', 'web_directory']);
        $resolver->setDefaults([
            'format'        => 'thumbnail',
            'update_cache'  => true,
            'preview'       => true,
            'web_directory' => $this->webDirectory
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['preview']   = $options['preview'];
        $view->vars['image_url'] = null;
        $view->vars['assets_version'] = null;

        if ($view->vars['preview']) {
            $view->vars['image_url'] = $this->getImagePath($form, $options);

            $accessor = PropertyAccess::createPropertyAccessor();
            $parentData = $form->getParent()->getData();
            $view->vars['assets_version'] = $this->generateTimestamp($accessor, $parentData);
        }
    }

    /**
     * Build image path
     *
     * @param FormInterface $form
     * @param array         $options
     * @return string
     */
    private function getImagePath(FormInterface $form, array $options)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $property = $form->getName().'Filename';
        $parentData = $form->getParent()->getData();
        $imagePath = $accessor->getValue($parentData, $property);
        if (empty($imagePath)) {
            return null;
        }

        if (true === $options['update_cache']) {
            $imagePath .= sprintf('?v=%d', $this->generateTimestamp($accessor, $parentData));
        }

        return str_replace('{-imgformat-}', $options['format'], $options['web_directory'].$imagePath);
    }

    /**
     * Generate timestamp data used for cache management
     *
     * @param PropertyAccessorInterface $accessor
     * @param mixed                     $parentData
     * @return int
     */
    private function generateTimestamp(PropertyAccessorInterface $accessor, $parentData)
    {
        if ($accessor->isReadable($parentData, 'updatedAt') && $accessor->getValue($parentData, 'updatedAt') instanceof \DateTime) {
            return $accessor->getValue($parentData, 'updatedAt')->format('U');
        }

        return date('U');
    }
}

