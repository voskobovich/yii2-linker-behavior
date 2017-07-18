<?php

namespace voskobovich\linker\interfaces;

use voskobovich\linker\AssociativeRowCondition;

/**
 * Interface ManyToManyUpdaterInterface.
 */
interface ManyToManyUpdaterInterface extends UpdaterInterface
{
    /**
     * Set additional attribute values of viaTable.
     *
     * @param $value
     */
    public function setViaTableAttributesValue($value);

    /**
     * Get additional attribute values of viaTable.
     *
     * @return array
     */
    public function getViaTableAttributesValue();

    /**
     * Get additional value of attribute in viaTable.
     *
     * @param string $attributeName
     * @param int $relatedPk
     * @param AssociativeRowCondition $rowCondition
     *
     * @return mixed
     */
    public function getViaTableAttributeValue($attributeName, $relatedPk, AssociativeRowCondition $rowCondition);

    /**
     * Set condition used to processed old records from viaTable.
     *
     * @param $value
     */
    public function setViaTableCondition($value);

    /**
     * Get condition used to processed old records from viaTable.
     *
     * @return array
     */
    public function getViaTableCondition();
}
