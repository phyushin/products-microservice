<?php

use Illuminate\Database\Seeder;
use App\Http\Controllers\ImportController;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ImportController = new ImportController();
        $request = \Illuminate\Http\Request::create(
            '/v1/import',
            'POST',
            [], [], [], [],
            file_get_contents(database_path().'/seeds/products.csv')
        );
        $response = $ImportController->create($request, new \App\Product);
        if ($response->status() != 201) {
            throw new Exception('Error inserting data: '.$response->getContent());
        }

        $body = json_decode($response->getContent(), true);
        if ($body['failed_total'] > 0) {
            throw new Exception('failed_total greater than 0'.$response->getContent());
        }

        if ($body['total_imported'] == 0) {
            throw new Exception('No data was imported'.$response->getContent());
        }

        return $body['total_imported'].' rows inserted';
    }
}
