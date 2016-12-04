<?php

namespace voskobovich\linker;

use voskobovich\linker\interfaces\LinkerBehaviorInterface;
use voskobovich\linker\interfaces\ManyToManyUpdaterInterface;
use voskobovich\linker\interfaces\OneToManyUpdaterInterface;
use voskobovich\linker\updaters\ManyToManyUpdater;
use voskobovich\linker\updaters\OneToManyUpdater;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\base\ErrorException;

/**
 * Class LinkerBehavior
 * @package voskobovich\linker
 *
 * See README.md for examples
 */
class LinkerBehavior extends Behavior implements LinkerBehaviorInterface
{
    /**
     * Stores a list of Relations configured in the Behavior in the Model.
     * Configurable property.
     *
     * Example 1:
     * ```
     * 'relations' => [
     *     'author_ids' => 'authors',
     * ]
     * ```
     *
     * Example 2:
     * ```
     * 'relations' => [
     *     'reviews',
     *     'updater' => [
     *         'fallbackValue' => 17,
     *     ]
     * ]
     * ```
     * @var array
     */
    public $relations = [];

    /**
     * Stores values of relation attributes. All entries in this array are considered
     * dirty (changed) attributes and will be saved in saveRelations().
     *
     * Example array structure:
     * [
     *     'author_ids' => [1,78,9]
     * ]
     *
     * @var array
     */
    private $dirtyValueOfAttributes = [];

    /**
     * Used to store fields that this behavior creates.
     * Each field refers to a relation and has optional getters and setters.
     *
     * Example array structure:
     * [
     *     'author_ids' = [
     *         'attribute' => 'author_ids'
     *     ],
     *     'author_ids_json' = [
     *         'attribute' => 'author_ids',
     *         'get' => {callable function}
     *     ],
     *     'relation_ids' = [
     *         'attribute' => 'relation_ids',
     *         'set' => {callable function}
     *     ],
     * ]
     *
     * @var array
     */
    private $dynamicFieldsOfModel = [];

    /**
     * Build updater instance
     * @param $relationConfig
     * @param $defaultUpdaterClass
     * @return object
     */
    private function buildUpdater($relationConfig, $defaultUpdaterClass)
    {
        if (!is_array($relationConfig)) {
            $relationConfig = [$relationConfig];
        }

        if (empty($relationConfig['updater']['class'])) {
            if (!empty($relationConfig['updater'])) {
                $relationConfig['updater']['class'] = $defaultUpdaterClass;
            } else {
                $relationConfig['updater'] = [
                    'class' => $defaultUpdaterClass
                ];
            }
        }

        return Yii::createObject($relationConfig['updater']);
    }

    /**
     * Events list
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'saveRelations',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveRelations',
        ];
    }

    /**
     * Invokes init of parent class and assigns proper values to internal fields variable
     */
    public function init()
    {
        parent::init();

        // Configure dynamicFieldsOfModel
        foreach ($this->relations as $dynamicAttributeName => $dynamicAttributeParams) {
            // Add primary field
            $this->dynamicFieldsOfModel[$dynamicAttributeName] = [
                'attribute' => $dynamicAttributeName,
            ];
            if (isset($dynamicAttributeParams['get'])) {
                $this->dynamicFieldsOfModel[$dynamicAttributeName]['get'] = $dynamicAttributeParams['get'];
            }
            if (isset($dynamicAttributeParams['set'])) {
                $this->dynamicFieldsOfModel[$dynamicAttributeName]['set'] = $dynamicAttributeParams['set'];
            }

            // Add secondary fields of primary field
            if (isset($dynamicAttributeParams['fields'])) {
                foreach ($dynamicAttributeParams['fields'] as $fieldName => $adjustments) {
                    $fullFieldName = $dynamicAttributeName . '_' . $fieldName;
                    if (isset($this->dynamicFieldsOfModel[$fullFieldName])) {
                        throw new ErrorException("Ambiguous field name definition: {$fullFieldName}");
                    }

                    $this->dynamicFieldsOfModel[$fullFieldName] = [
                        'attribute' => $dynamicAttributeName,
                    ];
                    if (isset($adjustments['get'])) {
                        $this->dynamicFieldsOfModel[$fullFieldName]['get'] = $adjustments['get'];
                    }
                    if (isset($adjustments['set'])) {
                        $this->dynamicFieldsOfModel[$fullFieldName]['set'] = $adjustments['set'];
                    }
                }
            }
        }
    }

    /**
     * Save all dirty (changed) relation values ($this->dirtyValueOfAttributes) to the database
     * @throws ErrorException
     */
    public function saveRelations()
    {
        /** @var ActiveRecord $primaryModel */
        $primaryModel = $this->owner;
        $primaryModelPk = $primaryModel->getPrimaryKey();

        if (is_array($primaryModelPk)) {
            throw new ErrorException('This behavior does not support composite primary keys');
        }

        foreach ($this->relations as $dynamicAttributeName => $dynamicAttributeParams) {
            $relationName = $this->getRelationName($dynamicAttributeName);
            $relation = $primaryModel->getRelation($relationName);

            if (!$this->hasDirtyValueOfAttribute($dynamicAttributeName)) {
                continue;
            }

            if (!empty($relation->via) && $relation->multiple) {
                // Many-to-many

                $updater = $this->buildUpdater($dynamicAttributeParams, ManyToManyUpdater::className());
                if (!$updater instanceof ManyToManyUpdaterInterface) {
                    throw new InvalidConfigException(
                        'Updater class must implement ' .
                        'the interface "voskobovich\linker\interfaces\ManyToManyUpdaterInterface"'
                    );
                }
            } elseif (!empty($relation->link) && $relation->multiple) {
                // One-to-many on the many side

                $updater = $this->buildUpdater($dynamicAttributeParams, OneToManyUpdater::className());
                if (!$updater instanceof OneToManyUpdaterInterface) {
                    throw new InvalidConfigException(
                        'Updater class must implement ' .
                        'the interface "voskobovich\linker\interfaces\OneToManyUpdaterInterface"'
                    );
                }
            } else {
                throw new ErrorException('Relationship type is not supported.');
            }

            $updater->setBehavior($this);
            $updater->setRelation($relation);
            $updater->setAttributeName($dynamicAttributeName);
            $updater->save();
        }
    }

    /**
     * Check if an attribute is dirty and must be saved (its new value exists)
     * @param string $attributeName
     * @return null
     */
    public function hasDirtyValueOfAttribute($attributeName)
    {
        return isset($this->dirtyValueOfAttributes[$attributeName]);
    }

    /**
     * Get value of a dirty attribute by name
     * @param string $attributeName
     * @return null
     */
    public function getDirtyValueOfAttribute($attributeName)
    {
        return $this->dirtyValueOfAttributes[$attributeName];
    }

    /**
     * Get parameters of a field
     * @param string $fieldName
     * @return mixed
     * @throws ErrorException
     */
    public function getFieldParams($fieldName)
    {
        if (!array_key_exists($fieldName, $this->dynamicFieldsOfModel)) {
            throw new ErrorException('Parameter "' . $fieldName . '" does not exist');
        }

        return $this->dynamicFieldsOfModel[$fieldName];
    }

    /**
     * Get parameters of a relation
     * @param string $attributeName
     * @return mixed
     * @throws ErrorException
     */
    public function getRelationParams($attributeName)
    {
        if (!array_key_exists($attributeName, $this->relations)) {
            throw new ErrorException('Parameter "' . $attributeName . '" does not exist.');
        }

        return $this->relations[$attributeName];
    }

    /**
     * Get name of a relation
     * @param string $attributeName
     * @return null
     */
    public function getRelationName($attributeName)
    {
        $params = $this->getRelationParams($attributeName);

        if (is_string($params)) {
            return $params;
        }

        if (is_array($params) && !empty($params[0])) {
            return $params[0];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return array_key_exists($name, $this->dynamicFieldsOfModel)
            ? true
            : parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return array_key_exists($name, $this->dynamicFieldsOfModel)
            ? true
            : parent::canSetProperty($name, $checkVars = true);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        $fieldParams = $this->getFieldParams($name);
        $attributeName = $fieldParams['attribute'];
        $relationName = $this->getRelationName($attributeName);

        if ($this->hasDirtyValueOfAttribute($attributeName)) {
            $value = $this->getDirtyValueOfAttribute($attributeName);
        } else {
            /** @var ActiveRecord $owner */
            $owner = $this->owner;
            $relation = $owner->getRelation($relationName);

            /** @var ActiveRecord $foreignModel */
            $foreignModel = Yii::createObject($relation->modelClass);
            $value = $relation->select($foreignModel->getPrimaryKey())->column();
        }

        if (empty($fieldParams['get'])) {
            return $value;
        }

        return call_user_func($fieldParams['get'], $value);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $fieldParams = $this->getFieldParams($name);
        $attributeName = $fieldParams['attribute'];

        if (!empty($fieldParams['set'])) {
            $this->dirtyValueOfAttributes[$attributeName] = call_user_func($fieldParams['set'], $value);
            return;
        }

        $this->dirtyValueOfAttributes[$attributeName] = $value;
    }
}
