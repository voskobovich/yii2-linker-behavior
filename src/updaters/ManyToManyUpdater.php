<?php

namespace voskobovich\linker\updaters;

use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class ManyToManyUpdater
 * @package voskobovich\linker\updaters
 */
class ManyToManyUpdater extends BaseManyToManyUpdater
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
            $junctionTable = $junctionModelClass::tableName();
            list($junctionColumn) = array_keys($via->link);
        } else {
            // viaTable()
            list($junctionTable) = array_values($this->getRelation()->via->from);
            list($junctionColumn) = array_keys($this->getRelation()->via->link);
        }

        list($relatedColumn) = array_values($this->getRelation()->link);

        $connection = $primaryModel::getDb();
        $transaction = $connection->beginTransaction();
        try {
            // Remove old relations
            $connection->createCommand()
                ->delete($junctionTable, ArrayHelper::merge(
                    [$junctionColumn => $primaryModelPk],
                    $this->getDeleteCondition()
                ))
                ->execute();

            // Write new relations
            if (!empty($bindingKeys)) {
                $junctionRows = [];

                $viaTableAttributes = $this->getViaTableAttributes();

                foreach ($bindingKeys as $relatedPk) {
                    $row = [$primaryModelPk, $relatedPk];

                    // Calculate additional viaTable values
                    foreach (array_keys($viaTableAttributes) as $viaTableColumnName) {
                        $row[] = $this->getViaTableAttributeValue($viaTableColumnName, $relatedPk);
                    }

                    array_push($junctionRows, $row);
                }

                $cols = [$junctionColumn, $relatedColumn];

                // Additional viaTable columns
                foreach (array_keys($viaTableAttributes) as $viaTableColumnName) {
                    $cols[] = $viaTableColumnName;
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