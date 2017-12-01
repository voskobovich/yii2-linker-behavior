<?php

namespace voskobovich\linker;

use yii\base\BaseObject;

/**
 * Class AssociativeRowCondition.
 */
class AssociativeRowCondition extends BaseObject
{
    /**
     * The state of the associative row.
     *
     * @var bool
     */
    public $isNewRecord = true;

    /**
     * This is a old value of an attribute from a associative row.
     *
     * @var null
     */
    public $oldValue;
}
