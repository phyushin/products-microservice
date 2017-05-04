<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-02
 * Time: 17:58
 */

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Database\Query\Builder;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;

class ProductController extends BaseController
{
    protected $product;

    /** @var  Manager $fractalManager */
    protected $fractalManager;

    public function __construct(Manager $manager, DataArraySerializer $arraySerializer, Product $product)
    {
        $this->product = $product;
        $this->fractalManager = $manager;
        $this->fractalManager->setSerializer($arraySerializer);
    }

    public function index(Request $request)
    {
        $currentCursor = (int)base64_decode($request->input('cursor'));
        $previousCursor = (int)base64_decode($request->input('previous'));
        $limit = (int)$request->input('limit', 500);

        if ($currentCursor) {
            /** @var Builder $query */
            $query = $this->product
                ->skip($currentCursor)
                ->take($limit)
                ->get();
        } else {
            /** @var Builder $query */
            $query = $this->product
                ->take($limit)->get();
        }

        $nextCursor = count($query) < $limit ? null : urlencode(base64_encode(($currentCursor + $limit)));
        $cursor = new Cursor(
            urlencode(base64_encode($currentCursor)),
            urlencode(base64_encode($previousCursor)),
            $nextCursor,
            count($query)
        );
    }

    public function show(Request $request, $productPLU)
    {
        // Return a singular Product by PLU
    }
}