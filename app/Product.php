<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sku', 'plu', 'name', 'size', 'size_sort'
    ];

    public function uniqueProducts()
    {
        return $this->groupBy('plu');
    }

    public function sortSizes($product)
    {
        $validSizeSorts = $this->validSizeSorts();
        $collection = new Collection($product['sizes']);
        $sorted = $collection->sortBy(function ($size, $key) use ($validSizeSorts, $product) {
            return array_search(
                $size['size'],
                $validSizeSorts[$product['size_sort']]
            );
        });
        return $sorted->values();
    }

    public function validSizeSorts()
    {
        $shoeGenerator = function ($isChild = false, $largest = 12, $smallest = 1, $step = 0.5) {
            $output = [];
            for ($i=$smallest;$i<=$largest;$i+=$step) {
                $output[] = $isChild ? $i . ' (Child)' : $i;
            }
            return $output;
        };

        return [
            'SHOE_UK' => array_merge($shoeGenerator(true), $shoeGenerator(false, 14)),
            'SHOE_EU' => $shoeGenerator(false, 50, 20, 1),
            'CLOTHING_SHORT' => [
                'XS',
                'S',
                'M',
                'L',
                'XL',
                'XXL',
                'XXXL',
                'XXXXL',
            ]
        ];
    }
}
