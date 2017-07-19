<?php

namespace unit;

use models\Book;
use models\BookCustomDefaults;
use models\BookJson;
use models\BookJsonFields;
use Yii;
use yii\codeception\TestCase;
use yii\Helpers\ArrayHelper;
use yii\Helpers\Json;

/**
 * Class ManyToOneTest.
 */
class ManyToOneTest extends TestCase
{
    /**
     * Config Path.
     *
     * @var string
     */
    public $appConfig = '@tests/unit/_config.php';

    /**
     * Save model and Reload.
     *
     * @param \yii\db\ActiveRecord $modelClass
     * @param int $id the PK of model
     * @param $loadData
     *
     * @return mixed
     */
    protected function saveAndReload($modelClass, $id, $loadData)
    {
        $model = $modelClass::findOne($id);
        $this->assertNotEmpty($model, 'Load model');

        $this->assertTrue($model->load($loadData), 'Load POST data');
        $this->assertTrue($model->save(), 'Save model');

        $model = $modelClass::findOne($id);
        $this->assertNotEmpty($model, 'Reload model');

        return $model;
    }

    /**
     * Set empty data.
     */
    public function testDoNothing()
    {
        $postData = [
            'Book' => [],
        ];

        $model = $this->saveAndReload(
            new Book(),
            3,
            $postData
        );

        $this->assertCount(3, $model->reviews, 'Review count after save');
    }

    /**
     * Save one-to-many record.
     */
    public function testSaveOneToMany()
    {
        $model = $this->saveAndReload(
            new Book(),
            3,
            [
                'Book' => [
                    'review_ids' => [2, 4],
                ],
            ]
        );

        //must have two reviews
        $this->assertCount(2, $model->reviews, 'Review count after save');

        //must have reviews 2 and 4
        $reviewKeys = array_keys($model->getReviews()->indexBy('id')->all());
        $this->assertContains(2, $reviewKeys, 'Saved review exists');
        $this->assertContains(4, $reviewKeys, 'Saved review exists');
    }

    /**
     * Reset one-to-many record.
     */
    public function testResetOneToMany()
    {
        $model = $this->saveAndReload(
            new Book(),
            3,
            [
                'Book' => [
                    'review_ids' => [],
                ],
            ]
        );

        //must have zero reviews
        $this->assertCount(0, $model->reviews, 'Review count after save');
    }

    /**
     * Save one-to-many record in JSON format.
     */
    public function testSaveOneToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson(),
            3,
            [
                'BookJson' => [
                    'review_ids' => '[2, 4]',
                ],
            ]
        );

        //must have two reviews
        $this->assertCount(2, $model->reviews, 'Review count after save');

        //must have reviews 2 and 4
        $reviewKeys = array_keys($model->getReviews()->indexBy('id')->all());
        $this->assertContains(2, $reviewKeys, 'Saved review exists');
        $this->assertContains(4, $reviewKeys, 'Saved review exists');
    }

    /**
     * Reset one-to-many record in JSON format.
     */
    public function testResetOneToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson(),
            3,
            [
                'BookJson' => [
                    'review_ids' => '[]',
                ],
            ]
        );

        //must have zero reviews
        $this->assertCount(0, $model->reviews, 'Review count after save');
    }

    public function testResetWithDefaultNone()
    {
        $model = BookCustomDefaults::findOne(3);
        $this->assertNotEmpty($model, 'Load model');

        //this model is attached to reviews 1, 2 and 3

        $this->assertTrue($model->load(['BookCustomDefaults' => ['review_ids_none' => []]]), 'Load POST data');
        $this->assertTrue($model->save(), 'Save model');

        $result = Yii::$app->db
            ->createCommand('SELECT id, book_id FROM review WHERE id IN (1, 2, 3)')
            ->queryAll();
        //get data from DB directly
        $newValues = ArrayHelper::map($result, 'id', 'book_id');
        $this->assertEquals(null, $newValues[1], 'Default value saved');
        $this->assertEquals(null, $newValues[2], 'Default value saved');
        $this->assertEquals(null, $newValues[3], 'Default value saved');
    }

    public function testResetWithDefaultNull()
    {
        $model = BookCustomDefaults::findOne(3);
        $this->assertNotEmpty($model, 'Load model');

        //this model is attached to reviews 1, 2 and 3

        $this->assertTrue($model->load(['BookCustomDefaults' => ['review_ids_null' => []]]), 'Load POST data');
        $this->assertTrue($model->save(), 'Save model');

        $result = Yii::$app->db
            ->createCommand('SELECT id, book_id FROM review WHERE id IN (1, 2, 3)')
            ->queryAll();
        //get data from DB directly
        $newValues = ArrayHelper::map($result, 'id', 'book_id');
        $this->assertEquals(null, $newValues[1], 'Default value saved');
        $this->assertEquals(null, $newValues[2], 'Default value saved');
        $this->assertEquals(null, $newValues[3], 'Default value saved');
    }

    public function testResetWithDefaultConstant()
    {
        $model = BookCustomDefaults::findOne(3);
        $this->assertNotEmpty($model, 'Load model');

        //this model is attached to reviews 1, 2 and 3
        $this->assertTrue($model->load(['BookCustomDefaults' => ['review_ids_constant' => []]]), 'Load POST data');
        $this->assertTrue($model->save(), 'Save model');

        $result = Yii::$app->db
            ->createCommand('SELECT id, book_id FROM review WHERE id IN (1, 2, 3)')
            ->queryAll();
        //get data from DB directly
        $newValues = ArrayHelper::map($result, 'id', 'book_id');
        $this->assertEquals(7, $newValues[1], 'Default value saved');
        $this->assertEquals(7, $newValues[2], 'Default value saved');
        $this->assertEquals(7, $newValues[3], 'Default value saved');
    }

    public function testResetWithDefaultClosure()
    {
        $model = BookCustomDefaults::findOne(3);
        $this->assertNotEmpty($model, 'Load model');

        //this model is attached to reviews 1, 2 and 3

        $data = [
            'BookCustomDefaults' => [
                'review_ids_closure' => [],
            ],
        ];

        $this->assertTrue($model->load($data), 'Load POST data');
        $this->assertTrue($model->save(), 'Save model');

        $result = Yii::$app->db
            ->createCommand('SELECT id, book_id FROM review WHERE id IN (1, 2, 3)')
            ->queryAll();

        //get data from DB directly
        $newValues = ArrayHelper::map($result, 'id', 'book_id');

        $this->assertEquals(17, $newValues[1], 'Default value saved');
        $this->assertEquals(17, $newValues[2], 'Default value saved');
        $this->assertEquals(17, $newValues[3], 'Default value saved');
    }

    public function testCustomGettersSetters()
    {
        $reviewIds = [1, 2, 4];
        $reviewIdsJson = Json::encode($reviewIds);
        $reviewIdsImplode = implode(',', $reviewIds);

        //assign and getters
        $model = new BookJsonFields();
        $model->review_ids = $reviewIds;

        $this->assertEquals($model->review_ids, $reviewIds, 'Direct getter');
        $this->assertEquals($model->review_ids_json, $reviewIdsJson, 'JSON getter');
        $this->assertEquals($model->review_ids_implode, $reviewIdsImplode, 'Implode getter');

        //test json setters
        $model = new BookJsonFields();
        $model->review_ids_json = $reviewIdsJson;
        $this->assertEquals($model->review_ids, $reviewIds, 'JSON setter');

        //test implode setter
        $model = new BookJsonFields();
        $model->review_ids_implode = $reviewIdsImplode;
        $this->assertEquals($model->review_ids, $reviewIds, 'Implode setter');
    }
}
