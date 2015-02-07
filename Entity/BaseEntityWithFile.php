<?php

namespace Novaway\Bundle\FileManagementBundle\Entity;

/**
 * Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile
 *
 * Extend your entities with this abstract class to add File management.
 */
abstract class BaseEntityWithFile implements BaseEntityWithFileInterface
{
    /**
     * {@inheritdoc}
     */
    public function getter($propertyName, $filenameOnly = false)
    {
        return sprintf('get%s%s', ucfirst($propertyName), $filenameOnly ? 'Filename' : '');
    }

    /**
     * {@inheritdoc}
     */
    public function setter($propertyName, $filenameOnly = false)
    {
        return sprintf('set%s%s', ucfirst($propertyName), $filenameOnly ? 'Filename' : '');
    }

    /**
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
            if (count($arguments) !== 1) {
                throw new \InvalidArgumentException();
            }

            $prop = reset($arguments);
            if (!property_exists($this, $prop)) {
                throw new \InvalidArgumentException();
            }

            return $this->$prop;
        }

        if (!method_exists($this, $method) && preg_match('#^(get|set) {1}([a-z0-1]+)$#i', $method, $match)) {
            $property = lcfirst($match[2]);
            if ($match[1] === 'get') {
                return $this->$property;
            }

            if (count($arguments) == 1) {
                $this->$property = $arguments[0];
            }

            throw new \InvalidArgumentException();
        }

        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }

        throw new \BadMethodCallException(sprintf("BadMethodCallException: method (%s) doesn't exist for %s class", $method, get_class($this)));
    }
}

