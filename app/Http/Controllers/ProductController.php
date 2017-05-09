<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-02
 * Time: 17:58
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Transformers\ProductTransformer;
use App\Product;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Database\Query\Builder;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                ->uniqueProducts()
                ->skip($currentCursor)
                ->take($limit)
                ->get();
        } else {
            /** @var Builder $query */
            $query = $this->product
                ->uniqueProducts()
                ->take($limit)->get();
        }

        $nextCursor = count($query) < $limit ? null : urlencode(base64_encode(($currentCursor + $limit)));
        $cursor = new Cursor(
            urlencode(base64_encode($currentCursor)),
            urlencode(base64_encode($previousCursor)),
            $nextCursor,
            count($query)
        );

        $resource = new Collection($query, new ProductTransformer());
        $resource->setCursor($cursor);

        return $this->fractalManager->createData($resource)->toArray();
    }

    public function show(Request $request, $productPLU)
    {
        // Return a singular Product by PLU
        $productQuery = $this->product
            ->where('plu', '=', $productPLU)
            ->get();
        $aggProduct = $productQuery->groupBy('plu')->toArray();

        if (count($aggProduct) < 1) {
            throw new NotFoundHttpException('Product PLU not found');
        }

        $product = $aggProduct[$productPLU][0];
        $product['sizes'] = [];
        foreach($aggProduct[$productPLU] as $prod) {
            $product['sizes'][] = [
                'SKU' => $prod['sku'],
                'size' => $prod['size'],
            ];
        }

        $product['sizes'] = $this->product->sortSizes($product);

        $this->fractalManager->setSerializer(new ArraySerializer());

        $resource = new Item($product, new ProductTransformer());
        return $this->fractalManager->createData($resource)->toArray();
    }
}