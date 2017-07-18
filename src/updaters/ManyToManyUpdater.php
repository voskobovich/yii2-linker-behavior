<?php

namespace voskobovich\linker\updaters;

use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class ManyToManyUpdater.
 */
class ManyToManyUpdater extends BaseManyToManyUpdater
{
    /**
     * @throws Exception
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function save()
    {
        /** @var ActiveRecord $primaryModel */
        $primaryModel = $this->getBehavior()->owner;
        $primaryModelPkValue = $primaryModel->getPrimaryKey();
        $attributeName = $this->getAttributeName();
        $relation = $this->getRelation();

        $bindingKeys = $this->getBehavior()
            ->getDirtyValueOfAttribute($attributeName);

        // Assuming junction column is visible from the primary model connection
        if (is_array($relation->via)) {
            // via()
            $via = $relation->via[1];
            /** @var ActiveRecord $junctionModelClass */
            $junctionModelClass = $via->modelClass;
            $junctionTableName = $junctionModelClass::tableName();
            list($junctionColumnName) = array_keys($via->link);
        } else {
            // viaTable()
            list($junctionTableName) = array_values($relation->via->from);
            list($junctionColumnName) = array_keys($relation->via->link);
        }

        list($relatedColumnName) = array_values($relation->link);

        $dbConnection = $primaryModel::getDb();
        $transaction = $dbConnection->beginTransaction();
        try {
            // Remove old relations
            $dbConnection->createCommand()
                ->delete(
                    $junctionTableName,
                    ArrayHelper::merge(
                        [$junctionColumnName => $primaryModelPkValue],
                        $this->getViaTableCondition()
                    )
                )
                ->execute();

            // Write new relations
            if (false === empty($bindingKeys)) {
                $junctionRows = [];

                $viaTableAttributes = $this->getViaTableAttributesValue();
                $viaTableColumnNames = array_keys($viaTableAttributes);

                foreach ($bindingKeys as $relatedPkValue) {
                    $row = [$primaryModelPkValue, $relatedPkValue];

                    // Calculate additional viaTable values
                    foreach ($viaTableColumnNames as $viaTableColumnName) {
                        $row[] = $this->getViaTableAttributeValue(
                            $viaTableColumnName,
                            $relatedPkValue,
                            new $this->rowConditionClass()
                        );
                    }

                    $junctionRows[] = $row;
                }

                $junctionTableColumnNames = [$junctionColumnName, $relatedColumnName];

                // Additional viaTable columns
                foreach ($viaTableColumnNames as $viaTableColumnName) {
                    $junctionTableColumnNames[] = $viaTableColumnName;
                }

                $dbConnection->createCommand()
                    ->batchInsert(
                        $junctionTableName,
                        $junctionTableColumnNames,
                        $junctionRows
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
