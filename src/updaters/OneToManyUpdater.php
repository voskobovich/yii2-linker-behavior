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
        $primaryModelPk = $primaryModel->getPrimaryKey();

        $bindingKeys = $this->getBehavior()->getNewValue($this->getAttributeName());

        // HasMany, primary model HAS MANY foreign models, must update foreign model table
        /** @var ActiveRecord $foreignModel */
        $foreignModel = Yii::createObject($this->getRelation()->modelClass);
        $manyTable = $foreignModel->tableName();

        list($manyTableFkColumn) = array_keys($this->getRelation()->link);
        $manyTableFkValue = $primaryModelPk;
        list($manyTablePkColumn) = ($foreignModel->primaryKey());

        $connection = $foreignModel::getDb();
        $transaction = $connection->beginTransaction();

        try {
            // Remove old relations
            $connection->createCommand()
                ->update(
                    $manyTable,
                    [$manyTableFkColumn => $this->getFallbackValue()],
                    [$manyTableFkColumn => $manyTableFkValue])
                ->execute();

            // Write new relations
            if (!empty($bindingKeys)) {
                $connection->createCommand()
                    ->update(
                        $manyTable,
                        [$manyTableFkColumn => $manyTableFkValue],
                        ['in', $manyTablePkColumn, $bindingKeys])
                    ->execute();
            }
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }
    }
}