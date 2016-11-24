<?php

namespace voskobovich\linker\interfaces;

use yii\db\ActiveQuery;

/**
 * Interface UpdaterInterface
 * @package voskobovich\linker\interfaces
 */
interface UpdaterInterface
{
    /**
     * Set behavior object
     * @param LinkerBehaviorInterface $behavior
     */
    public function setBehavior(LinkerBehaviorInterface $behavior);

    /**
     * Get behavior object
     * @return LinkerBehaviorInterface
     */
    public function getBehavior();

    /**
     * Set relation object
     * @param ActiveQuery $value
     */
    public function setRelation(ActiveQuery $value);

    /**
     * Get relation object
     * @return ActiveQuery
     */
    public function getRelation();

    /**
     * Set attribute name
     * @param string $value
     */
    public function setAttributeName($value);

    /**
     * Get attribute name
     * @return string
     */
    public function getAttributeName();

    /**
     * Save relations
     */
    public function save();
}