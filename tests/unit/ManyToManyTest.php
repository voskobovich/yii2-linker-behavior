<?php

namespace unit;

use models\Book;
use models\BookBadFields;
use models\BookJson;
use models\BookJsonFields;
use yii\base\ErrorException;
use yii\codeception\TestCase;
use yii\Helpers\Json;

/**
 * Class ManyToManyTest.
 */
class ManyToManyTest extends TestCase
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

        $this->assertCount(1, $model->authors, 'Author count after save');
    }

    /**
     * Test Save many-to-many record.
     */
    public function testSaveManyToMany()
    {
        $postData = [
            'Book' => [
                'author_ids' => [7, 9, 8],
            ],
        ];

        $model = $this->saveAndReload(
            new Book(),
            5,
            $postData
        );

        //must have three authors
        $this->assertCount(3, $model->authors, 'Author count after save');

        //must have authors 7, 8, and 9
        $authorKeys = array_keys($model->getAuthors()->indexBy('id')->all());
        $this->assertContains(7, $authorKeys, 'Saved author exists');
        $this->assertContains(8, $authorKeys, 'Saved author exists');
        $this->assertContains(9, $authorKeys, 'Saved author exists');
    }

    /**
     * Reset many-to-many record.
     */
    public function testResetManyToMany()
    {
        $model = $this->saveAndReload(
            new Book(),
            5,
            [
                'Book' => [
                    'author_ids' => [],
                ],
            ]
        );

        //must have three authors
        $this->assertCount(0, $model->authors, 'Author count after save');
    }

    /**
     * Save many-to-many record in JSON format.
     */
    public function testSaveManyToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson(),
            5,
            [
                'BookJson' => [
                    'author_ids' => '[7, 9, 8]',
                ],
            ]
        );

        //must have three authors
        $this->assertCount(3, $model->authors, 'Author count after save');

        //must have authors 7, 8, and 9
        $authorKeys = array_keys($model->getAuthors()->indexBy('id')->all());
        $this->assertContains(7, $authorKeys, 'Saved author exists');
        $this->assertContains(8, $authorKeys, 'Saved author exists');
        $this->assertContains(9, $authorKeys, 'Saved author exists');
    }

    /**
     * Reset many-to-many record in JSON format.
     */
    public function testResetManyToManyJson()
    {
        $model = $this->saveAndReload(
            new BookJson(),
            5,
            [
                'BookJson' => [
                    'author_ids' => '[]',
                ],
            ]
        );

        //must have three authors
        $this->assertCount(0, $model->authors, 'Author count after save');
    }

    public function testCustomGettersSetters()
    {
        $authorIds = [5, 6];
        $authorIdsJson = Json::encode($authorIds);

        //assign and getters
        $model = new BookJsonFields();
        $model->author_ids = $authorIds;

        $this->assertEquals($model->author_ids, $authorIds, 'Direct getter');

        $this->assertEquals($model->author_ids_json, $authorIdsJson, 'JSON getter');

        //test json setters
        $model = new BookJsonFields();
        $model->author_ids_json = $authorIdsJson;
        $this->assertEquals($model->author_ids, $authorIds, 'JSON setter');

        //test implode setter for non-existence where appropriate
        $model = new BookJsonFields();
        $this->assertFalse(isset($model->author_ids_implode), 'Non-existence of setter where not declared');
    }

    /**
     * Test Bad Fields.
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
