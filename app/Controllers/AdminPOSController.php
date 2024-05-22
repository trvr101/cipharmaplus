<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\OrderModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\AuditModel;
use App\Models\NotificationModel;

class AdminPOSController extends ResourceController
{
    public function index()
    {
    }


    public function ClearCurrentTransaction()
    {
        $user = new UserModel();
        $audit = new AuditModel();
        $token = $this->request->getVar('token');
        $order_token = $this->request->getVar('order_token');
        $profile = $user->where('token', $token)->first();
        //Delete the  $audit->where('token_code', $order_token)

        return $this->respond();
    }
    //Create a new table for this hold_orders_tbl 
    public function HoldCurrentTransaction()
    {
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $order_token = $this->request->getVar('order_token');
        $profile = $user->where('token', $token)->first();


        return $this->respond();
    }
}
