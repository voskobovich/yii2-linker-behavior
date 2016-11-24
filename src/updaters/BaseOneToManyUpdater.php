<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\interfaces\OneToManyUpdaterInterface;

/**
 * Class BaseOneToManyUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseOneToManyUpdater extends BaseUpdater implements OneToManyUpdaterInterface
{
    /**
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * Set default value for an attribute
     * @param string $value
     */
    public function setDefaultValue($value)
    {
        if (is_callable($value)) {
            $this->defaultValue = call_user_func($value, $this);
        } else {
            $this->defaultValue = $value;
        }
    }

    /**
     * Get default value for an attribute (used for 1-N relations)
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Save relation
     */
    abstract public function save();
}