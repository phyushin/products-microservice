<?php
/**
 * Created by PhpStorm.
 * User: adam
 * Date: 2017-05-02
 * Time: 17:58
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Database\Query\Builder;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;

class ProductController extends BaseController
{
    /** @var  \Illuminate\Database\Connection $db */
    protected $db;

    /** @var  Manager $fractalManager */
    protected $fractalManager;

    public function __construct(Manager $manager, DataArraySerializer $arraySerializer)
    {
        $this->db = app('db')->connection();
        $this->fractalManager = $manager;
        $this->fractalManager->setSerializer($arraySerializer);
    }

    public function index(Request $request)
    {
        // Return all products
    }

    public function show(Request $request, $productPLU)
    {
        // Return a singular Product by PLU
    }
}