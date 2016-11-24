<?php

namespace unit;

use data\Book;
use data\BookBadFields;
use data\BookCustomDefaults;
use data\BookJson;
use data\BookJsonFields;
use Yii;
use yii\codeception\TestCase;
use yii\base\ErrorException;
use yii\Helpers\ArrayHelper;
use yii\Helpers\Json;

/**
 * Class BehaviorTest
 * @package unit
 */
class BehaviorTest extends TestCase
{
    /**
     * Config Path
     * @var string
     */
    public $appConfig = '@tests/unit/_config.php';

    /**
     * Save model and Reload
     * @param \yii\db\ActiveRecord $modelClass
     * @param integer $id the PK of model
     * @param $loadData
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
     * Set empty data
     */
    public function testDoNothing()
    {
        $model = $this->saveAndReload(
            new Book,
            3,
            [
                'Book' => []
            ]
        );

        $this->assertEquals(1, count($model->authors), 'Author count after save');
        $this->assertEquals(3, count($model->reviews), 'Review count after save');
    }

    /**
     * Test Save many-to-many record
     */
    public function testSaveManyToMany()
    {
        $model = $this->saveAndReload(
            new Book,
            5,
            [
                'Book' => [
                    'author_ids' => [7, 9, 8]
                ]
            ]
        );

        //must have three authors
        $this->assertEquals(3, count($model->authors), 'Author count after save');

        //must have authors 7, 8, and 9
        $authorKeys = array_keys($model->getAuthors()->indexBy('id')->all());
        $this->assertContains(7, $authorKeys, 'Saved author exists');
        $this->assertContains(8, $authorKeys, 'Saved author exists');
        $this->assertContains(9, $authorKeys, 'Saved author exists');
    }

    /**
     * Reset many-to-many record
     */
    public function testResetManyToMany()
    {
        $model = $this->saveAndReload(
            new Book,
            5,
            [
                'Book' => [
                    'author_ids' => []
                ]
            ]
        );

        //must have three authors
        $this->assertEquals(0, count($model->authors), 'Author count after save');
    }

    /**
     * Save one-to-many record
     */
    public function testSaveOneToMany()
    {
        $model = $this->saveAndReload(
            new Book,
            3,
            [
                'Book' => [
                    'review_ids' => [2, 4]
                ]
            ]
        );

        //must have two reviews
        $this->assertEquals(2, count($model->reviews), 'Review count after save');

        //must have reviews 2 and 4
        $reviewKeys = array_keys($model->getReviews()->indexBy('id')->all());
        $this->assertContains(2, $reviewKeys, 'Saved review exists');
        $this->assertContains(4, $reviewKeys, 'Saved review exists');
    }

    /**
     * Reset one-to-many record
     */
    public function testResetOneToMany()
    {
        $model = $this->saveAndReload(
            new Book,
            3,
            [
                'Book' => [
                    'review_ids' => []
                ]
            ]
        );

        //must have zero reviews
        $this->assertEquals(0, count($model->reviews), 'Review count after save');
    }

    /**
     * Save many-to-many record in JSON format
     */
    public function testSaveManyToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson,
            5,
            [
                'BookJson' => [
                    'author_ids' => '[7, 9, 8]'
                ]
            ]
        );

        //must have three authors
        $this->assertEquals(3, count($model->authors), 'Author count after save');

        //must have authors 7, 8, and 9
        $authorKeys = array_keys($model->getAuthors()->indexBy('id')->all());
        $this->assertContains(7, $authorKeys, 'Saved author exists');
        $this->assertContains(8, $authorKeys, 'Saved author exists');
        $this->assertContains(9, $authorKeys, 'Saved author exists');
    }

    /**
     * Reset many-to-many record in JSON format
     */
    public function testResetManyToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson,
            5,
            [
                'BookJson' => [
                    'author_ids' => '[]'
                ]
            ]
        );

        //must have three authors
        $this->assertEquals(0, count($model->authors), 'Author count after save');
    }

    /**
     * Save one-to-many record in JSON format
     */
    public function testSaveOneToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson,
            3,
            [
                'BookJson' => [
                    'review_ids' => '[2, 4]'
                ]
            ]
        );

        //must have two reviews
        $this->assertEquals(2, count($model->reviews), 'Review count after save');

        //must have reviews 2 and 4
        $reviewKeys = array_keys($model->getReviews()->indexBy('id')->all());
        $this->assertContains(2, $reviewKeys, 'Saved review exists');
        $this->assertContains(4, $reviewKeys, 'Saved review exists');
    }

    /**
     * Reset one-to-many record in JSON format
     */
    public function testResetOneToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson,
            3,
            [
                'BookJson' => [
                    'review_ids' => '[]'
                ]
            ]
        );

        //must have zero reviews
        $this->assertEquals(0, count($model->reviews), 'Review count after save');
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

        $this->assertTrue($model->load(['BookCustomDefaults' => ['review_ids_closure' => []]]), 'Load POST data');
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

        $authorIds = [5, 6];
        $authorIdsJson = Json::encode($authorIds);

        //assign and getters
        $model = new BookJsonFields;
        $model->review_ids = $reviewIds;
        $model->author_ids = $authorIds;

        $this->assertEquals($model->review_ids, $reviewIds, 'Direct getter');
        $this->assertEquals($model->author_ids, $authorIds, 'Direct getter');

        $this->assertEquals($model->author_ids_json, $authorIdsJson, 'JSON getter');
        $this->assertEquals($model->review_ids_json, $reviewIdsJson, 'JSON getter');

        $this->assertEquals($model->review_ids_implode, $reviewIdsImplode, 'Implode getter');

        //test json setters
        $model = new BookJsonFields;
        $model->review_ids_json = $reviewIdsJson;
        $this->assertEquals($model->review_ids, $reviewIds, 'JSON setter');
        $model->author_ids_json = $authorIdsJson;
        $this->assertEquals($model->author_ids, $authorIds, 'JSON setter');

        //test implode setter for non-existence where appropriate
        $model = new BookJsonFields;
        $this->assertFalse(isset($model->author_ids_implode), 'Non-existence of setter where not declared');

        //test implode setter
        $model = new BookJsonFields;
        $model->review_ids_implode = $reviewIdsImplode;
        $this->assertEquals($model->review_ids, $reviewIds, 'Implode setter');
    }

    /**
     * Test Bad Fields
     */
    public function testBadFields()
    {
        $caught = false;
        try {
            new BookBadFields();
        } catch (ErrorException $e) {
            $caught = true;
        }

        $this->assertTrue($caught, 'Caught exception');
    }
}
