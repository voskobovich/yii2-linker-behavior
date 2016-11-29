<?php

namespace voskobovich\linker\updaters;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Class OneToManyUpdater
 * @package voskobovich\linker\updaters
 */
class OneToManyUpdater extends BaseOneToManyUpdater
{
    /**
     * @throws Exception
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

        // HasMany, primary model HAS MANY foreign models, must update foreign model table
        /** @var ActiveRecord $foreignModel */
        $foreignModel = Yii::createObject($relation->modelClass);
        $manyTableName = $foreignModel->tableName();

        list($manyTableFkColumnName) = array_keys($relation->link);
        $manyTableFkValue = $primaryModelPkValue;
        list($manyTablePkColumnName) = ($foreignModel->primaryKey());

        $dbConnection = $foreignModel::getDb();
        $transaction = $dbConnection->beginTransaction();

        try {
            // Remove old relations
            $dbConnection->createCommand()
                ->update(
                    $manyTableName,
                    [$manyTableFkColumnName => $this->getFallbackValue()],
                    [$manyTableFkColumnName => $manyTableFkValue]
                )
                ->execute();

            // Write new relations
            if (!empty($bindingKeys)) {
                $dbConnection->createCommand()
                    ->update(
                        $manyTableName,
                        [$manyTableFkColumnName => $manyTableFkValue],
                        ['in', $manyTablePkColumnName, $bindingKeys]
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
