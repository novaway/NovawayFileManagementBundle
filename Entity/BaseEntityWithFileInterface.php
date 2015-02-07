<?php

namespace Novaway\Bundle\FileManagementBundle\Entity;

interface BaseEntityWithFileInterface
{
    /**
     * Build Getter string for a property
     *
     * @param string  $propertyName The property whose getter will bi return
     * @param boolean $filenameOnly Set to TRUE to return the property filename getter
     *                                     FALSE to return the getter for the property itself
     * @return string The getter method
     */
    public function getter($propertyName, $filenameOnly = false);

    /**
     * Build Setter string for a property
     *
     * @param string  $propertyName The property whose setter will bi return
     * @param boolean $filenameOnly Set to TRUE to return the property filename setter
     *                                     FALSE to return the setter for the property itself
     * @return string The setter method
     */
    public function setter($propertyName, $filenameOnly = false);
}
