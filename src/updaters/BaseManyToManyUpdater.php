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
    private $_viaTableAttributes = [];

    /**
     * Custom condition for remove record from viaTable
     * @var array
     */
    private $_deleteCondition = [];

    /**
     * Set additional attributes of viaTable
     * @param $value
     */
    public function setViaTableAttributes($value)
    {
        if (!is_array($value)) {
            throw new InvalidParamException('Value must be an array.');
        }

        $this->_viaTableAttributes = $value;
    }

    /**
     * Get additional attributes of viaTable
     * @return array
     */
    public function getViaTableAttributes()
    {
        return $this->_viaTableAttributes;
    }

    /**
     * Get additional value of attribute in viaTable
     * @param string $viaTableAttributeName
     * @param integer $relatedPk
     * @param bool $isNewRecord
     * @return mixed
     */
    public function getViaTableAttributeValue($viaTableAttributeName, $relatedPk, $isNewRecord = true)
    {
        $viaTableAttributes = $this->getViaTableAttributes();

        if (!isset($viaTableAttributes[$viaTableAttributeName])) {
            return null;
        }

        if (is_callable($viaTableAttributes[$viaTableAttributeName])) {
            $closure = $viaTableAttributes[$viaTableAttributeName];
            return call_user_func($closure, $this, $relatedPk, $isNewRecord);
        }

        return $viaTableAttributes[$viaTableAttributeName];
    }

    /**
     * Set condition used to delete old records from viaTable.
     * @param $value
     */
    public function setDeleteCondition($value)
    {
        $this->_deleteCondition = $value;
    }

    /**
     * Get condition used to delete old records from viaTable.
     * @return array
     */
    public function getDeleteCondition()
    {
        return $this->_deleteCondition;
    }

    /**
     * Save relations
     */
    abstract public function save();
}