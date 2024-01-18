<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;

use App\Models\AuditModel;
use App\Models\UserModel;
use App\Models\ProductModel;

class OrderController extends ResourceController
{
    public function index()
    {
        $main = new OrderModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function SalesTransaction()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        if ($profile) {
            $branchId = $profile['branch_id'];
        }
        $data = $order->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->findAll();

        return $this->respond($data);
    }
    public function HoldSalesTransaction()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        if ($profile) {
            $branchId = $profile['branch_id'];
        }
        $data = $order->where('branch_id', $branchId)
            ->where('status', 'pending')
            ->findAll();

        return $this->respond($data);
    }
    public function RemoveUnusedTransaction()
    {
        $order = new OrderModel();

        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));

        $data = $order->where('status', 'processing')
            ->where('created_at >', $yesterday)
            ->findAll();

        return $this->respond($data);
    }
}