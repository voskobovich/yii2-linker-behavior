<?php

namespace models;

use voskobovich\linker\LinkerBehavior;
use Yii;

/**
 * Class BookCustomDefaults.
 */
class BookCustomDefaults extends Book
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['review_ids_none', 'review_ids_null', 'review_ids_constant', 'review_ids_closure'], 'safe'],
            [['name', 'year'], 'required'],
            [['year'], 'integer'],
            [['name'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \yii\db\Exception
     */
    public function behaviors()
    {
        return [
            'linkerBehavior' => [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'review_ids_none' => [
                        'reviews',
                    ],
                    'review_ids_null' => [
                        'reviews',
                        'updater' => [
                            'fallbackValue' => null,
                        ],
                    ],
                    'review_ids_constant' => [
                        'reviews',
                        'updater' => [
                            'fallbackValue' => 7,
                        ],
                    ],
                    'review_ids_closure' => [
                        'reviews',
                        'updater' => [
                            'fallbackValue' => function ($updater) {
                                return Yii::$app->db
                                    ->createCommand('SELECT value FROM settings WHERE key="default_review"')
                                    ->queryScalar();
                            },
                        ],
                    ],
                ],
            ],
        ];
    }
}
