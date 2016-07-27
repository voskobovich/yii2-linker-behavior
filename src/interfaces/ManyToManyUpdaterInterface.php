<?php

namespace voskobovich\linker\interfaces;

/**
 * Interface ManyToManyUpdaterInterface
 * @package voskobovich\linker\interfaces
 */
interface ManyToManyUpdaterInterface
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
    public function saveManyToManyRelation($relation, $attributeName);
}