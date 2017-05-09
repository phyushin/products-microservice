<?php

use Illuminate\Support\Facades\Artisan;
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-09
 * Time: 18:05
 */
class ProductTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class'=>'ProductTableSeeder']);
    }
    public function testIndex()
    {
        $this->get('/v1/product?limit=2')
            ->seeStatusCode(200)
            ->seeJson([
                'PLU' => 'AAA',
                'name' => 'Random product AAA.'
            ])
            ->seeJson([
                'PLU' => 'AAB',
                'name' => 'Random product AAB.'
            ])
            ->seeJson(['meta'=>[
                'cursor'=>[
                    'count' => 2,
                    'current' => 'MA%3D%3D',
                    'prev' => 'MA%3D%3D',
                    'next' => 'Mg%3D%3D'
                ]
            ]]);
    }

    public function testIndexCursor()
    {
        $this->get('/v1/product?limit=2&cursor=Mg%3D%3D')
            ->seeStatusCode(200)
            ->seeJson([
                'PLU' => 'AAC',
                'name' => 'Random product AAC.'
            ])
            ->seeJson([
                'PLU' => 'AAE',
                'name' => 'Random product AAE.'
            ])
            ->seeJson(['meta'=>[
                'cursor'=>[
                    'count' => 2,
                    'current' => 'Mg%3D%3D',
                    'prev' => 'MA%3D%3D',
                    'next' => 'NA%3D%3D'
                ]
            ]]);
    }

    public function testShowClothingShort()
    {
        $expectedJson = [
                'PLU' => 'AAC',
                'name' => 'Random product AAC.',
                'sizes' => [
                    [
                        'SKU' => '114',
                        'size' => 'XS'
                    ],
                    [
                        'SKU' => '116',
                        'size' => 'M'
                    ],
                    [
                        'SKU' => '115',
                        'size' => 'L'
                    ],
                    [
                        'SKU' => '112',
                        'size' => 'XXL'
                    ],
                    [
                        'SKU' => '117',
                        'size' => 'XXXL'
                    ],
                    [
                        'SKU' => '113',
                        'size' => 'XXXXL'
                    ],
                ]
            ];
        $this->get('/v1/product/AAC')
            ->seeStatusCode(200)
            // Assert ordering is also correct
            ->assertEquals(json_encode($expectedJson), $this->response->getContent());
    }

    public function testShowShoeUK()
    {
        $expectedJson = [
            'PLU' => 'AAE',
            'name' => 'Random product AAE.',
            'sizes' => [
                [
                    'SKU' => '128',
                    'size' => '4.5 (Child)'
                ],
                [
                    'SKU' => '127',
                    'size' => '9 (Child)'
                ],
                [
                    'SKU' => '125',
                    'size' => '11 (Child)'
                ],
                [
                    'SKU' => '126',
                    'size' => '1'
                ],
            ]
        ];
        $this->get('/v1/product/AAE')
            ->seeStatusCode(200)
            // Assert ordering is also correct
            ->assertEquals(json_encode($expectedJson), $this->response->getContent());
    }

    public function testShowShoeEU()
    {
        $expectedJson = [
            'PLU' => 'AAA',
            'name' => 'Random product AAA.',
            'sizes' => [
                [
                    'SKU' => '101',
                    'size' => '22'
                ],
                [
                    'SKU' => '105',
                    'size' => '25'
                ],
                [
                    'SKU' => '100',
                    'size' => '28'
                ],
                [
                    'SKU' => '104',
                    'size' => '32'
                ],
                [
                    'SKU' => '103',
                    'size' => '36'
                ],
                [
                    'SKU' => '102',
                    'size' => '38'
                ],
            ]
        ];
        $this->get('/v1/product/AAA')
            ->seeStatusCode(200)
            // Assert ordering is also correct
            ->assertEquals(json_encode($expectedJson), $this->response->getContent());
    }

    public function testProductNotFound()
    {
        $this->get('/v1/product/INVALID')
            ->seeStatusCode(404)
            ->seeJson([
                'error' => 'Product PLU not found'
            ]);
    }

}