<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\interfaces\LinkerBehaviorInterface;
use voskobovich\linker\interfaces\UpdaterInterface;
use yii\base\Behavior;
use yii\base\Object;
use yii\db\ActiveQuery;

/**
 * Class BaseUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseUpdater extends Object implements UpdaterInterface
{
    /**
     * Behavior object
     * @var LinkerBehaviorInterface|Behavior
     */
    private $behavior;

    /**
     * Relation object
     * @var ActiveQuery
     */
    private $relation;

    /**
     * Current attribute name
     * @var string
     */
    private $attributeName;

    /**
     * Set behavior object
     * @param LinkerBehaviorInterface $behavior
     */
    public function setBehavior(LinkerBehaviorInterface $behavior)
    {
        $this->behavior = $behavior;
    }

    /**
     * Set behavior object
     * @return LinkerBehaviorInterface|Behavior
     */
    public function getBehavior()
    {
        return $this->behavior;
    }

    /**
     * Set relation object
     * @param ActiveQuery $value
     */
    public function setRelation(ActiveQuery $value)
    {
        $this->relation = $value;
    }

    /**
     * Get relation object
     * @return ActiveQuery
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Set attribute name
     * @param string $value
     */
    public function setAttributeName($value)
    {
        $this->attributeName = $value;
    }

    /**
     * Get attribute name
     * @return string
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }
}