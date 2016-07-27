<?php

namespace voskobovich\linker\updaters;

use voskobovich\linker\interfaces\ManyToManyUpdaterInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class ManyToManyUpdater
 * @package voskobovich\linker\updaters
 */
class ManyToManyUpdater extends BaseUpdater implements ManyToManyUpdaterInterface
{
    /**
     * @param ActiveQuery $relation
     * @param string $attributeName
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function saveManyToManyRelation($relation, $attributeName)
    {
        /** @var ActiveRecord $primaryModel */
        $primaryModel = $this->_behavior->owner;
        $primaryModelPk = $primaryModel->getPrimaryKey();

        $bindingKeys = $this->_behavior->getNewValue($attributeName);

        // Assuming junction column is visible from the primary model connection
        if (is_array($relation->via)) {
            // via()
            $via = $relation->via[1];
            /** @var ActiveRecord $junctionModelClass */
            $junctionModelClass = $via->modelClass;
            $junctionTable = $junctionModelClass::tableName();
            list($junctionColumn) = array_keys($via->link);
        } else {
            // viaTable()
            list($junctionTable) = array_values($relation->via->from);
            list($junctionColumn) = array_keys($relation->via->link);
        }

        list($relatedColumn) = array_values($relation->link);

        $connection = $primaryModel::getDb();
        $transaction = $connection->beginTransaction();
        try {
            // Remove old relations
            $connection->createCommand()
                ->delete($junctionTable, ArrayHelper::merge(
                    [$junctionColumn => $primaryModelPk],
                    $this->_behavior->getCustomDeleteCondition($attributeName)
                ))
                ->execute();

            // Write new relations
            if (!empty($bindingKeys)) {
                $junctionRows = [];

                $viaTableParams = $this->_behavior->getViaTableParams($attributeName);

                foreach ($bindingKeys as $relatedPk) {
                    $row = [$primaryModelPk, $relatedPk];

                    // Calculate additional viaTable values
                    foreach (array_keys($viaTableParams) as $viaTableColumn) {
                        $row[] = $this->_behavior->getViaTableValue($attributeName, $viaTableColumn, $relatedPk);
                    }

                    array_push($junctionRows, $row);
                }

                $cols = [$junctionColumn, $relatedColumn];

                // Additional viaTable columns
                foreach (array_keys($viaTableParams) as $viaTableColumn) {
                    $cols[] = $viaTableColumn;
                }

                $connection->createCommand()
                    ->batchInsert($junctionTable, $cols, $junctionRows)
                    ->execute();
            }
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
}