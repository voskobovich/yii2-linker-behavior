<?php

namespace voskobovich\linker\interfaces;

/**
 * Interface OneToManyUpdaterInterface.
 */
interface OneToManyUpdaterInterface extends UpdaterInterface
{
    /**
     * Set default value for an attribute.
     *
     * @param string $value
     */
    public function setFallbackValue($value);

    /**
     * Get default value for an attribute (used for 1-N relations).
     *
     * @return mixed
     */
    public function getFallbackValue();
}
