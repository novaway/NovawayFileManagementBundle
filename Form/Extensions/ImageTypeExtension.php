<?php

namespace Novaway\Bundle\FileManagementBundle\Form\Extensions;

use Symfony\Component\Form\AbstractTypeExtension;

class ImageTypeExtension extends AbstractTypeExtension
{
    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'image';
    }
}
