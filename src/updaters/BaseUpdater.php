<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\interfaces\LinkerBehaviorInterface;
use yii\base\Behavior;
use yii\base\Object;
use yii\db\ActiveQuery;

/**
 * Class BaseUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseUpdater extends Object
{
    /**
     * Behavior object
     * @var LinkerBehaviorInterface|Behavior
     */
    private $_behavior;

    /**
     * Relation object
     * @var ActiveQuery
     */
    private $_relation;

    /**
     * Current attribute name
     * @var string
     */
    private $_attributeName;

    /**
     * Set behavior object
     * @param LinkerBehaviorInterface $behavior
     * @return mixed
     */
    public function setBehavior(LinkerBehaviorInterface $behavior)
    {
        $this->_behavior = $behavior;
    }

    /**
     * Set behavior object
     * @return LinkerBehaviorInterface|Behavior
     */
    public function getBehavior()
    {
        return $this->_behavior;
    }

    /**
     * Set relation object
     * @param ActiveQuery $value
     */
    public function setRelation(ActiveQuery $value)
    {
        $this->_relation = $value;
    }

    /**
     * Get relation object
     * @return ActiveQuery
     */
    public function getRelation()
    {
        return $this->_relation;
    }

    /**
     * Set attribute name
     * @param string $value
     */
    public function setAttributeName($value)
    {
        $this->_attributeName = $value;
    }

    /**
     * Get attribute name
     * @return string
     */
    public function getAttributeName()
    {
        return $this->_attributeName;
    }
}