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
    public function transform($product)
    {
        $return = [
            'PLU'=>$product['plu'],
            'name'=>$product['name'],
        ];

        if (isset($product['sizes'])) {
            $return['sizes'] = $product['sizes'];
        }

        return $return;
    }
}