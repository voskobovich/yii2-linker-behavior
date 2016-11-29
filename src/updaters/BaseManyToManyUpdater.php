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
     * Condition used to process old records from viaTable.
     * @var array
     */
    private $viaTableCondition = [];

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

        if (!array_key_exists($attributeName, $viaTableAttributes)) {
            return null;
        }

        if (is_callable($viaTableAttributes[$attributeName])) {
            return call_user_func(
                $viaTableAttributes[$attributeName],
                $this,
                $relatedPk,
                $isNewRecord
            );
        }

        return $viaTableAttributes[$attributeName];
    }

    /**
     * Set condition used to process old records from viaTable.
     * @param $value
     */
    public function setViaTableCondition($value)
    {
        $this->viaTableCondition = $value;
    }

    /**
     * Get condition used to process old records from viaTable.
     * @return array
     */
    public function getViaTableCondition()
    {
        return $this->viaTableCondition;
    }

    /**
     * Save relations
     */
    abstract public function save();
}