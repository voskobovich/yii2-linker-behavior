<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\AssociativeRowCondition;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class ManyToManySmartUpdater
 * @package voskobovich\linker\updaters
 */
class ManyToManySmartUpdater extends BaseManyToManyUpdater
{
    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function save()
    {
        /** @var ActiveRecord $primaryModel */
        $primaryModel = $this->getBehavior()->owner;
        $primaryModelPkValue = $primaryModel->getPrimaryKey();
        $relation = $this->getRelation();
        $attributeName = $this->getAttributeName();

        $bindingKeys = $this->getBehavior()
            ->getDirtyValueOfAttribute($attributeName);

        // Assuming junction column is visible from the primary model connection
        if (is_array($relation->via)) {
            // via()
            $via = $relation->via[1];
            /** @var ActiveRecord $junctionModelClass */
            $junctionModelClass = $via->modelClass;
            $viaTableName = $junctionModelClass::tableName();
            list($junctionColumnName) = array_keys($via->link);
        } else {
            // viaTable()
            list($viaTableName) = array_values($relation->via->from);
            list($junctionColumnName) = array_keys($relation->via->link);
        }

        list($relatedColumnName) = array_values($relation->link);

        $dbConnection = $primaryModel::getDb();
        $transaction = $dbConnection->beginTransaction();
        try {
            // Load current rows
            $currentRows = $primaryModel::find()
                ->from($viaTableName)
                ->where(ArrayHelper::merge(
                    [$junctionColumnName => $primaryModelPkValue],
                    $this->getViaTableCondition()
                ))
                ->indexBy($relatedColumnName)
                ->asArray()
                ->all();

            $currentKeys = array_map(
                function ($item) use ($relatedColumnName) {
                    return $item[$relatedColumnName];
                },
                $currentRows
            );

            if (!empty($bindingKeys)) {
                // Find removed relations
                $removedKeys = array_diff($currentKeys, $bindingKeys);
                // Find new relations
                $addedKeys = array_diff($bindingKeys, $currentKeys);
                // Find untouched relations
                $untouchedKeys = array_diff($currentKeys, $removedKeys, $addedKeys);

                $viaTableAttributes = $this->getViaTableAttributesValue();
                $viaTableColumnNames = array_keys($viaTableAttributes);

                $junctionColumnNames = [$junctionColumnName, $relatedColumnName];
                foreach ($viaTableColumnNames as $viaTableColumnName) {
                    $junctionColumnNames[] = $viaTableColumnName;
                }

                // Write new relations
                if (!empty($addedKeys)) {
                    $junctionRows = [];
                    foreach ($addedKeys as $addedKey) {
                        $row = [$primaryModelPkValue, $addedKey];

                        // Calculate additional viaTable values
                        foreach ($viaTableColumnNames as $viaTableColumnName) {
                            $row[] = $this->getViaTableAttributeValue(
                                $viaTableColumnName,
                                $addedKey,
                                new $this->rowConditionClass
                            );
                        }

                        array_push($junctionRows, $row);
                    }

                    $dbConnection->createCommand()
                        ->batchInsert($viaTableName, $junctionColumnNames, $junctionRows)
                        ->execute();
                }

                // Processing untouched relations
                if (!empty($untouchedKeys) && !empty($viaTableColumnNames)) {
                    foreach ($untouchedKeys as $untouchedKey) {
                        $currentRow = (array)$currentRows[$untouchedKey];

                        // Calculate additional viaTable values
                        $row = [];
                        foreach ($viaTableColumnNames as $viaTableColumnName) {
                            /** @var AssociativeRowCondition $rowCondition */
                            $rowCondition = Yii::createObject(
                                $this->rowConditionClass,
                                [
                                    'isNewRecord' => false,
                                    'oldValue' => $currentRow[$viaTableColumnName]
                                ]
                            );

                            $row[$viaTableColumnName] = $this->getViaTableAttributeValue(
                                $viaTableColumnName,
                                $untouchedKey,
                                $rowCondition
                            );
                        }

                        unset($currentRow[$junctionColumnName]);
                        unset($currentRow[$relatedColumnName]);

                        if (!array_diff_assoc($currentRow, $row)) {
                            continue;
                        }

                        $dbConnection->createCommand()
                            ->update(
                                $viaTableName,
                                $row,
                                [
                                    $junctionColumnName => $primaryModelPkValue,
                                    $relatedColumnName => $untouchedKey
                                ]
                            )
                            ->execute();
                    }
                }
            } else {
                $removedKeys = $currentKeys;
            }

            if (!empty($removedKeys)) {
                $dbConnection->createCommand()
                    ->delete(
                        $viaTableName,
                        ArrayHelper::merge(
                            [$junctionColumnName => $primaryModelPkValue],
                            [$relatedColumnName => $removedKeys],
                            $this->getViaTableCondition()
                        )
                    )
                    ->execute();
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }
    }
}
