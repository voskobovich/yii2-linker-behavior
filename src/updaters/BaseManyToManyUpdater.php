<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\AssociativeRowCondition;
use voskobovich\linker\interfaces\ManyToManyUpdaterInterface;
use yii\base\InvalidParamException;

/**
 * Class BaseManyToManyUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseManyToManyUpdater extends BaseUpdater implements ManyToManyUpdaterInterface
{
    /**
     * This is a object of current row state, that implement AssociativeRowCondition.
     * @var string
     */
    public $rowConditionClass = 'voskobovich\linker\AssociativeRowCondition';

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
     * @param AssociativeRowCondition $rowCondition
     * @return mixed
     */
    public function getViaTableAttributeValue($attributeName, $relatedPk, AssociativeRowCondition $rowCondition)
    {
        $viaTableAttributes = $this->getViaTableAttributesValue();

        if (!array_key_exists($attributeName, $viaTableAttributes)) {
            throw new InvalidParamException('Use a undefined attribute: ' . $attributeName . '.');
        }

        if (is_callable($viaTableAttributes[$attributeName])) {
            return call_user_func(
                $viaTableAttributes[$attributeName],
                $this,
                $relatedPk,
                $rowCondition
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