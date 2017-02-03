<?php

namespace voskobovich\linker;

use yii\base\Object;

/**
 * Class AssociativeRowCondition
 * @package voskobovich\linker
 */
class AssociativeRowCondition extends Object
{
    /**
     * The state of the associative row.
     * @var bool
     */
    public $isNewRecord = true;

    /**
     * This is a old value of an attribute from a associative row.
     * @var null
     */
    public $oldValue = null;
}
