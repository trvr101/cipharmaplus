<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\NotificationModel;

class NotificationController extends ResourceController
{
    public function index()
    {
        //
        $main = new NotificationModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}
