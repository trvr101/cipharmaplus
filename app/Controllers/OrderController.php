<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;

class OrderController extends ResourceController
{
    public function index()
    {
        $main = new OrderModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}
