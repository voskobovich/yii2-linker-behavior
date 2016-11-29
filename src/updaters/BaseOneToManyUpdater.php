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
    private $fallbackValue = null;

    /**
     * Set default value for an attribute
     * @param string $value
     */
    public function setFallbackValue($value)
    {
        if (is_callable($value)) {
            $this->fallbackValue = call_user_func($value, $this);
            return;
        }

        $this->fallbackValue = $value;
    }

    /**
     * Get default value for an attribute (used for 1-N relations)
     * @return mixed
     */
    public function getFallbackValue()
    {
        return $this->fallbackValue;
    }

    /**
     * Save relation
     */
    abstract public function save();
}