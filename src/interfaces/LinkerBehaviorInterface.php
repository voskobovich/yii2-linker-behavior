<?php

namespace voskobovich\linker\interfaces;

/**
 * Interface LinkerBehaviorInterface
 * @package voskobovich\linker\interfaces
 */
interface LinkerBehaviorInterface
{
    /**
     * Check if an attribute is dirty and must be saved (its new value exists)
     * @param string $attributeName
     * @return null
     */
    public function hasDirtyValueOfAttribute($attributeName);

    /**
     * Get value of a dirty attribute by name
     * @param string $attributeName
     * @return null
     */
    public function getDirtyValueOfAttribute($attributeName);

    /**
     * Get parameters of a field
     * @param string $fieldName
     * @return mixed
     */
    public function getFieldParams($fieldName);

    /**
     * Get parameters of a relation
     * @param string $attributeName
     * @return mixed
     */
    public function getRelationParams($attributeName);

    /**
     * Get name of a relation
     * @param string $attributeName
     * @return null
     */
    public function getRelationName($attributeName);
}