<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ActivityLogsModel;

class ActivityLogsController extends ResourceController
{
    public function index()
    {
        $main = new ActivityLogsModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}
