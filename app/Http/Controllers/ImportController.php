<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-03
 * Time: 22:51
 */

namespace App\Http\Controllers;

use App\Exceptions\InvalidFileException;
use App\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use League\Csv\AbstractCsv;
use League\Csv\Reader;

class ImportController extends BaseController
{
    /** @var  AbstractCsv $csvReader */
    protected $csvReader;

    public function create(Request $request, Product $product)
    {
        $reader = $this->getReaderFromRequest($request);
        $fails = [];
        $skipped = [];
        $imported = 0;

        $reader->each(function ($row) use ($product, &$fails, &$skipped, &$imported) {
            $insert = [
                'sku' => trim($row[0]),
                'plu' => trim($row[1]),
                'name' => trim($row[2]),
                'size' => trim($row[3]),
                'size_sort' => trim($row[4]),
            ];

            $validSizeSorts = $product->validSizeSorts();
            if (!array_key_exists($insert['size_sort'], $validSizeSorts)) {
                $fails[] = [
                    'row' => $row,
                    'error' => 'Invalid sizeSort',
                ];
                return true; // Log and skip
            }

            if (!in_array(
                $insert['size'],
                $validSizeSorts[$insert['size_sort']]
            )) {
                $fails[] = [
                    'row' => $row,
                    'error' => 'Invalid size of type ' . $insert['size_sort'],
                ];
                return true;
            }

            $skuExists = $product
                ->where('sku', '=', $insert['sku'])
                ->count('sku') > 0 ? true : false;

            if ($skuExists) {
                $skipped[] = $row;
                return true;
            }

            try {
                $product->insert($insert);
                $imported++;
            } catch (QueryException $e) {
                $fails[] = [
                    'row' => $row,
                    'error' => $e->getMessage()
                ];
            }
            return true;
        });

        return response([
                'total_imported' => $imported,
                'failed_total' => count($fails),
                'skipped_total' => count($skipped),
                'failed'=>$fails,
                'skipped' => $skipped,
                ],
            201,
            ['Content-Type' => 'application/json']);
    }

    public function getReaderFromRequest(Request $request)
    {
        // TODO: Handle file from form upload in addition to request content

        return Reader::createFromStream(
            $request->getContent(true)
        );
    }
}