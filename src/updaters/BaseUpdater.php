<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\interfaces\LinkerBehaviorInterface;
use yii\base\Behavior;
use yii\base\Object;

/**
 * Class BaseUpdater
 * @package voskobovich\linker\updaters
 */
abstract class BaseUpdater extends Object
{
    /**
     * @var LinkerBehaviorInterface|Behavior
     */
    protected $_behavior;

    /**
     * @param LinkerBehaviorInterface $behavior
     * @return mixed
     */
    public function setBehavior(LinkerBehaviorInterface $behavior)
    {
        $this->_behavior = $behavior;
    }
}