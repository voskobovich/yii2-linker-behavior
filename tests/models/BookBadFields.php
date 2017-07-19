<?php

namespace models;

use voskobovich\linker\LinkerBehavior;
use yii\helpers\Json;

/**
 * Class BookBadFields.
 */
class BookBadFields extends Book
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'linkerBehavior' => [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'author' => [
                        'authors',
                        'fields' => [
                            'ids_json' => [
                                'get' => function ($value) {
                                    return Json::encode($value);
                                },
                                'set' => function ($value) {
                                    return Json::decode($value);
                                },
                            ],
                        ],
                    ],
                    'author_ids' => [
                        'reviews',
                        'fields' => [
                            'json' => [
                                'get' => function ($value) {
                                    return Json::encode($value);
                                },
                                'set' => function ($value) {
                                    return Json::decode($value);
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
