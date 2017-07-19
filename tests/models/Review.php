<?php

namespace models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "review".
 *
 * @property int $id
 * @property int $book_id
 * @property string $comment
 * @property int $rating
 */
class Review extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'review';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['book_id', 'rating'], 'integer'],
            [['comment', 'rating'], 'required'],
            [['comment'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'book_id' => 'Book ID',
            'comment' => 'Comment',
            'rating' => 'Rating',
        ];
    }
}
