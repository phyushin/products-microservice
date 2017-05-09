<?php

/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-08
 * Time: 15:06
 */
class ImportTest extends TestCase
{
    public function testGetNotImplemented()
    {
        $this->get('/v1/import')
            ->seeStatusCode(501)
            ->contains('Not Implemented');
    }

    public function CSVProvider()
    {
        $validShoeEUCsv = '100, AAA, "Random product AAA.", "28", SHOE_EU
101, AAA, "Random product AAA.", "22", SHOE_EU
102, AAA, "Random product AAA.", "38", SHOE_EU
103, AAA, "Random product AAA.", "36", SHOE_EU
104, AAA, "Random product AAA.", "32", SHOE_EU
105, AAA, "Random product AAA.", "25", SHOE_EU';

        $validResponse = [
            'total_imported' => 6,
            'failed_total' => 0,
            'skipped_total' => 0,
            'failed' => [],
            'skipped' => [],
        ];

        $invalidSortSize = '100, AAA, "Random product AAA.", "28", BAD_TYPE
105, AAA, "Random product AAA.", "Bad Size", CLOTHING_SHORT';
        $invalidSortSizeResponse = $validResponse;
        $invalidSortSizeResponse['total_imported'] = 0;
        $invalidSortSizeResponse['failed_total'] = 2;
        $invalidSortSizeResponse['failed'] = [
            ['error'=>'Invalid sizeSort', 'row' => ['100',' AAA', "Random product AAA.", "28",' BAD_TYPE']],
            ['error'=>'Invalid size of type CLOTHING_SHORT', 'row' => ['105',' AAA', "Random product AAA.", "Bad Size",' CLOTHING_SHORT']],
        ];

        $duplicateSKU = '100, AAA, "Random product AAA.", "28", SHOE_EU
100, AAA, "Duplicate product AAA.", "28", SHOE_EU';
        $duplicateResponse = $validResponse;
        $duplicateResponse['total_imported'] = 1;
        $duplicateResponse['skipped_total'] = 1;
        $duplicateResponse['skipped'] = [
            ['100',' AAA', "Duplicate product AAA.", "28", ' SHOE_EU']
        ];

        $multiCase = $validShoeEUCsv.PHP_EOL.$invalidSortSize.PHP_EOL.$duplicateSKU;
        $multiCaseResponse = $validResponse;
        $multiCaseResponse['failed_total'] = $invalidSortSizeResponse['failed_total'];
        $multiCaseResponse['failed'] = $invalidSortSizeResponse['failed'];
        $multiCaseResponse['skipped'] = [
            $duplicateResponse['skipped'][0],
            ['100',' AAA', "Random product AAA.", "28", ' SHOE_EU']
        ];
        $multiCaseResponse['skipped_total'] = 2;

        return [
            [$validShoeEUCsv, 201, $validResponse],
            [$invalidSortSize, 201, $invalidSortSizeResponse],
            [$duplicateSKU, 201, $duplicateResponse],
            [$multiCase, 201, $multiCaseResponse],
        ];
    }

    /**
     * @dataProvider CSVProvider
     */
    public function testImportCSV($csv, $expectedStatusCode, $expectedResponse)
    {
        $request = \Illuminate\Http\Request::create(
            '/v1/import',
            'POST',
            [],[],[],[],
            $csv
        );

        $this->handle($request);
        $this->seeStatusCode($expectedStatusCode)
            ->seeJson($expectedResponse);
    }

    public function testSqlError()
    {
        $product = Mockery::mock(new \App\Product);
        $product->shouldReceive('where->count')
            ->andReturn(0);

        $exception = new \Illuminate\Database\QueryException('Mock Error', [], new Exception());
        $product->shouldReceive('insert')->andThrow($exception);

        $expectedResponse = [
            'total_imported' => 0,
            'failed_total' => 1,
            'skipped_total' => 0,
            'failed' => [
                ['row' => ['101', ' AAA', "Random product AAA.", "22", ' SHOE_EU'],
                'error' => ' (SQL: Mock Error)'],
            ],
            'skipped' => [],
        ];

        $csv = '101, AAA, "Random product AAA.", "22", SHOE_EU';
        $expectedStatusCode = 201;

        $request = \Illuminate\Http\Request::create(
            '/v1/import',
            'POST',
            [], [], [], [],
            $csv
        );

        $importController = new \App\Http\Controllers\ImportController();
        $response = $importController->create($request, $product);
        $this->response = $response;

        $this->seeStatusCode($expectedStatusCode)
            ->seeJson($expectedResponse);
    }
}