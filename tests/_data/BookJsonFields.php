<?php

namespace data;

use voskobovich\linker\LinkerBehavior;
use yii\helpers\Json;

/**
 * Class BookJsonFields
 * @package data
 *
 * @property string $review_list_json
 * @property string $review_list_implode
 * @property string $author_list_json
 * @property string $author_list_implode
 */
class BookJsonFields extends Book
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'linkerBehavior' => [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'author_list' => [
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
                    'review_list' => [
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
