<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\AuditModel;

class AuditController extends ResourceController
{
    public function index()
    {
        $main = new AuditModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}
