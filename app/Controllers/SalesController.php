<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\SalesModel;

class SalesController extends ResourceController
{
    public function index()
    {
        $main = new SalesModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}
