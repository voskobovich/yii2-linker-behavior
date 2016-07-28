<?php

namespace voskobovich\linker\interfaces;

/**
 * Interface OneToManyUpdaterInterface
 * @package voskobovich\linker\interfaces
 */
interface OneToManyUpdaterInterface extends UpdaterInterface
{
    /**
     * Get default value for an attribute (used for 1-N relations)
     * @param string $attributeName
     * @return mixed
     */
    public function getDefaultValue($attributeName);

    /**
     * Save relations
     */
    public function save();
}