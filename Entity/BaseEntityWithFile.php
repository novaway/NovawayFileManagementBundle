<?php

namespace Novaway\Bundle\FileManagementBundle\Entity;

/**
 * Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile
 *
 * Extend your entities with this abstract class to add File management.
 */
abstract class BaseEntityWithFile
{

    /**
     *
     * Magic method __call() override
     * Manages file properties getters and setters
     *
     * @param string $method    Name of the method to call
     * @param array  $arguments Array of parameters
     *
     * @return mixed Property value in case of getter, void in case of setter
     *
     * @throws \InvalidArgumentException if wrong number of arguments
     * @throws \BadMethodCallException   if method is neither a getter nor a setter
     */
    public function __call($method, $arguments)
    {
        if ('get' === $method) {
            if (count($arguments) == 1) {
                $prop = $arguments[0];
                if (property_exists($this, $prop)) {
                    return $this->$prop;
                } else {
                    throw new \InvalidArgumentException();
                }
            } else {
                throw new \InvalidArgumentException();
            }
        } elseif (!isset($this->$method) && preg_match('#^(get|set) {1}([a-z0-1]+)$#i',
                $method, $match)) {
            $property = lcfirst($match[2]);
            if ($match[1] === 'get') {
                return $this->$property;
            } else {
                if (count($arguments) == 1) {
                    $this->$property = $arguments[0];
                } else {
                    throw new \InvalidArgumentException();
                }
            }
        } else {
            throw new \BadMethodCallException();
        }
    }

}
