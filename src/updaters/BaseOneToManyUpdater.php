<?php

namespace voskobovich\linker\updaters;

/**
 * Class BaseOneToManyUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseOneToManyUpdater extends BaseUpdater
{
    /**
     * @var mixed
     */
    private $_defaultValue = null;

    /**
     * Set default value for an attribute
     * @param string $value
     * @return mixed
     */
    public function setDefaultValue($value)
    {
        if (is_callable($value)) {
            $this->_defaultValue = call_user_func($value, $this);
        } else {
            $this->_defaultValue = $value;
        }
    }

    /**
     * Get default value for an attribute (used for 1-N relations)
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }
}