<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-03
 * Time: 22:51
 */

namespace App\Http\Controllers;

use App\Exceptions\InvalidFileException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use League\Csv\AbstractCsv;
use League\Csv\Reader;

class ImportController extends BaseController
{
    /** @var  AbstractCsv $csvReader */
    protected $csvReader;

    public function create(Request $request)
    {
        /** @var \Illuminate\Database\Connection $db */
        $db = app('db')->connection()
        ;
        $reader = $this->getReaderFromRequest($request);
        $reader->each(function ($row) use ($db) {
            $insert = [
                'sku' => trim($row[0]),
                'plu' => trim($row[1]),
                'name' => trim($row[2]),
                'size' => trim($row[3]),
                'size_sort' => trim($row[4]),
            ];

            // TODO: Validate insert, skip if SKU exists

            $db->table('products')
                ->insert($insert);
        });
    }

    public function getReaderFromRequest(Request $request)
    {
        if($request->hasFile('csv')) {
            if ($request->file('csv')->isValid()) {
                return Reader::createFromFileObject(
                    $request->file('csv')->openFile()
                );
            }
            throw new InvalidFileException('Uploaded CSV is not a valid file request');
        }

        return Reader::createFromStream(
            $request->getContent(true)
        );
    }

    private function validateRequest($request)
    {

    }
}