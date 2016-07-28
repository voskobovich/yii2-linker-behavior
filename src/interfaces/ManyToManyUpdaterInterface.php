<?php

namespace voskobovich\linker\interfaces;

/**
 * Interface ManyToManyUpdaterInterface
 * @package voskobovich\linker\interfaces
 */
interface ManyToManyUpdaterInterface extends UpdaterInterface
{
    /**
     * Set additional attributes of viaTable
     * @param $value
     */
    public function setViaTableAttributes($value);

    /**
     * Get additional attributes of viaTable
     * @return array
     */
    public function getViaTableAttributes();

    /**
     * Get additional value of attribute in viaTable
     * @param string $viaTableAttribute
     * @param integer $relatedPk
     * @param bool $isNewRecord
     * @return mixed
     */
    public function getViaTableAttributeValue($viaTableAttribute, $relatedPk, $isNewRecord = true);

    /**
     * Set condition used to delete old records from viaTable.
     * @param $value
     */
    public function setDeleteCondition($value);

    /**
     * Get condition used to delete old records from viaTable.
     * @return array
     */
    public function getDeleteCondition();

    /**
     * Save relations
     */
    public function save();
}