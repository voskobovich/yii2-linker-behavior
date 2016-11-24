<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\interfaces\ManyToManyUpdaterInterface;
use yii\base\InvalidParamException;

/**
 * Class BaseManyToManyUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseManyToManyUpdater extends BaseUpdater implements ManyToManyUpdaterInterface
{
    /**
     * List of attributes and values by viaTable
     * @var array
     */
    private $viaTableAttributes = [];

    /**
     * Custom condition for remove record from viaTable
     * @var array
     */
    private $deleteCondition = [];

    /**
     * Set additional attributes of viaTable
     * @param $value
     */
    public function setViaTableAttributesValue($value)
    {
        if (!is_array($value)) {
            throw new InvalidParamException('Value must be an array.');
        }

        $this->viaTableAttributes = $value;
    }

    /**
     * Get additional attributes of viaTable
     * @return array
     */
    public function getViaTableAttributesValue()
    {
        return $this->viaTableAttributes;
    }

    /**
     * Get additional value of attribute in viaTable
     * @param string $attributeName
     * @param integer $relatedPk
     * @param bool $isNewRecord
     * @return mixed
     */
    public function getViaTableAttributeValue($attributeName, $relatedPk, $isNewRecord = true)
    {
        $viaTableAttributes = $this->getViaTableAttributesValue();

        if (!isset($viaTableAttributes[$attributeName])) {
            return null;
        }

        if (is_callable($viaTableAttributes[$attributeName])) {
            $closure = $viaTableAttributes[$attributeName];
            return call_user_func($closure, $this, $relatedPk, $isNewRecord);
        }

        return $viaTableAttributes[$attributeName];
    }

    /**
     * Set condition used to delete old records from viaTable.
     * @param $value
     */
    public function setViaTableDeleteCondition($value)
    {
        $this->deleteCondition = $value;
    }

    /**
     * Get condition used to delete old records from viaTable.
     * @return array
     */
    public function getViaTableDeleteCondition()
    {
        return $this->deleteCondition;
    }

    /**
     * Save relations
     */
    abstract public function save();
}