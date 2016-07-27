<?php

namespace voskobovich\linker\interfaces;

/**
 * Interface OneToManyUpdaterInterface
 * @package voskobovich\linker\interfaces
 */
interface OneToManyUpdaterInterface
{
    /**
     * @param LinkerBehaviorInterface $behavior
     * @return mixed
     */
    public function setBehavior(LinkerBehaviorInterface $behavior);

    /**
     * @param $relation
     * @param $attributeName
     */
    public function saveOneToManyRelation($relation, $attributeName);
}