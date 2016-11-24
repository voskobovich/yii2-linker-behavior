<?php

namespace data;

use voskobovich\linker\LinkerBehavior;
use Yii;

/**
 * Class BookCustomDefaults
 * @package data
 */
class BookCustomDefaults extends Book
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['review_list_none', 'review_list_null', 'review_list_constant', 'review_list_closure'], 'safe'],
            [['name', 'year'], 'required'],
            [['year'], 'integer'],
            [['name'], 'string', 'max' => 150]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'linkerBehavior' => [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'review_list_none' => [
                        'reviews',
                    ],
                    'review_list_null' => [
                        'reviews',
                        'updated' => [
                            'defaultValue' => null,
                        ]
                    ],
                    'review_list_constant' => [
                        'reviews',
                        'updater' => [
                            'defaultValue' => 7,
                        ]
                    ],
                    'review_list_closure' => [
                        'reviews',
                        'updater' => [
                            'defaultValue' => function ($updater) {
                                $db = Yii::$app->db;

                                /**
                                 * This is Example code.
                                 *
                                 * $db = $model::getDb();
                                 * OR
                                 * $secondaryModelClass = $model->getRelation($relationName)->modelClass;
                                 * $db = $secondaryModelClass::getDb();
                                 */

                                $defaultValue = $db
                                    ->createCommand('SELECT value FROM settings WHERE key="default_review"')
                                    ->queryScalar();

                                return $defaultValue;
                            },
                        ]
                    ]
                ]
            ]
        ];
    }
}
