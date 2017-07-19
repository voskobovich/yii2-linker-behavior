<?php

namespace models;

use voskobovich\linker\LinkerBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "book".
 *
 * @property int $id
 * @property string $name
 * @property int $year
 * @property int[] $review_ids
 * @property int[] $author_ids
 */
class Book extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'book';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_ids', 'review_ids'], 'safe'],
            [['name', 'year'], 'required'],
            [['year'], 'integer'],
            [['name'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'year' => 'Year',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::className(), ['id' => 'book_id'])
            ->viaTable('book_has_author', ['author_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Review::className(), ['book_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'linkerBehavior' => [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'author_ids' => 'authors',
                    'review_ids' => 'reviews',
                ],
            ],
        ];
    }
}
