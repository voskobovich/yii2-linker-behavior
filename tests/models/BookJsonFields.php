<?php

namespace models;

use voskobovich\linker\LinkerBehavior;
use yii\helpers\Json;

/**
 * Class BookJsonFields.
 *
 * @property string $review_ids_json
 * @property string $review_ids_implode
 * @property string $author_ids_json
 * @property string $author_ids_implode
 */
class BookJsonFields extends Book
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
                    'author_ids' => [
                        'authors',
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
                    'review_ids' => [
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
                            'implode' => [
                                'get' => function ($value) {
                                    return implode(',', $value);
                                },
                                'set' => function ($value) {
                                    return explode(',', $value);
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
