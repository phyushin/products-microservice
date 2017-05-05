<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-04
 * Time: 19:43
 */

namespace App\Http\Controllers\Transformers;


use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['sizes'];

    public function transform($product)
    {
        return [
            'PLU'=>$product['plu'],
            'name'=>$product['name'],
        ];
    }

    public function includeSizes($product)
    {
        return $this->collection($product['sizes'], function ($size) {
            return [
                'SKU' => $size['SKU'],
                'size' => $size['size'],
            ];
        });
    }
}