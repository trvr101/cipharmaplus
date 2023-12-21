<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\BranchModel;

class BranchController extends ResourceController
{
    public function index()
    {
        $main = new BranchModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function addBranch()
    {
    }
    private function generateCode($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}
