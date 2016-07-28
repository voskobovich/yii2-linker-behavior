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
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * Save relations
     */
    public function save();
}