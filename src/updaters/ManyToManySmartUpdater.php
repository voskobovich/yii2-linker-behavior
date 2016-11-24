<?php

namespace voskobovich\linker\updaters;

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
        $primaryModelPk = $primaryModel->getPrimaryKey();

        $bindingKeys = $this->getBehavior()->getNewValue($this->getAttributeName());

        // Assuming junction column is visible from the primary model connection
        if (is_array($this->getRelation()->via)) {
            // via()
            $via = $this->getRelation()->via[1];
            /** @var ActiveRecord $junctionModelClass */
            $junctionModelClass = $via->modelClass;
            $viaTableName = $junctionModelClass::tableName();
            list($junctionColumn) = array_keys($via->link);
        } else {
            // viaTable()
            list($viaTableName) = array_values($this->getRelation()->via->from);
            list($junctionColumn) = array_keys($this->getRelation()->via->link);
        }

        list($relatedColumn) = array_values($this->getRelation()->link);

        $connection = $primaryModel::getDb();
        $transaction = $connection->beginTransaction();
        try {
            // Load current rows
            $currentRows = $primaryModel::find()
                ->from($viaTableName)
                ->where(ArrayHelper::merge(
                    [$junctionColumn => $primaryModelPk],
                    $this->getViaTableDeleteCondition()
                ))
                ->indexBy($relatedColumn)
                ->asArray()
                ->all();

            $currentKeys = array_map(function ($item) use ($relatedColumn) {
                return $item[$relatedColumn];
            }, $currentRows);

            if (!empty($bindingKeys)) {
                // Find removed relations
                $removedKeys = array_diff($currentKeys, $bindingKeys);
                // Find new relations
                $addedKeys = array_diff($bindingKeys, $currentKeys);
                // Find untouched relations
                $untouchedKeys = array_diff($currentKeys, $removedKeys, $addedKeys);

                $viaTableAttributes = $this->getViaTableAttributesValue();
                $viaTableColumns = array_keys($viaTableAttributes);

                $junctionColumns = [$junctionColumn, $relatedColumn];
                foreach ($viaTableColumns as $viaTableColumnName) {
                    $junctionColumns[] = $viaTableColumnName;
                }

                // Write new relations
                if (!empty($addedKeys)) {
                    $junctionRows = [];
                    foreach ($addedKeys as $addedKey) {
                        $row = [$primaryModelPk, $addedKey];

                        // Calculate additional viaTable values
                        foreach ($viaTableColumns as $viaTableColumnName) {
                            $row[] = $this->getViaTableAttributeValue($viaTableColumnName, $addedKey);
                        }

                        array_push($junctionRows, $row);
                    }

                    $connection->createCommand()
                        ->batchInsert($viaTableName, $junctionColumns, $junctionRows)
                        ->execute();
                }

                // Processing untouched relations
                if (!empty($untouchedKeys) && !empty($viaTableColumns)) {
                    foreach ($untouchedKeys as $untouchedKey) {
                        // Calculate additional viaTable values
                        $row = [];
                        foreach ($viaTableColumns as $viaTableColumnName) {
                            $row[$viaTableColumnName] = $this->getViaTableAttributeValue(
                                $viaTableColumnName,
                                $untouchedKey,
                                false
                            );
                        }

                        $currentRow = (array)$currentRows[$untouchedKey];
                        unset($currentRow[$junctionColumn]);
                        unset($currentRow[$relatedColumn]);

                        if (array_diff_assoc($currentRow, $row)) {
                            $connection->createCommand()
                                ->update($viaTableName, $row, [
                                    $junctionColumn => $primaryModelPk,
                                    $relatedColumn => $untouchedKey
                                ])
                                ->execute();
                        }
                    }
                }
            } else {
                $removedKeys = $currentKeys;
            }

            if (!empty($removedKeys)) {
                $connection->createCommand()
                    ->delete($viaTableName, ArrayHelper::merge(
                        [$junctionColumn => $primaryModelPk],
                        [$relatedColumn => $removedKeys],
                        $this->getViaTableDeleteCondition()
                    ))
                    ->execute();
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }
    }
}