<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\CurrentTransactionModel;

use App\Models\OrderModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\AuditModel;


class CurrentTransactionController extends ResourceController
{
    public function index()
    {
        $main = new CurrentTransactionModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function AddItemToCurrentTransaction($token)
    {
        $UPCAndQuantity = $this->request->getVar('UPCAndQuantity');
        $user = new UserModel(); // ['user_id', 'first_name', 'last_name', 'email', 'user_password', 'phone', 'user_role', 'branch_id', 'status', 'token', 'created_at'];
        $product = new ProductModel(); //['product_id', 'upc', 'user_id', 'product_name', 'description', 'quantity', 'price', 'branch_id', 'category', 'status', 'created_at'];
        $order = new OrderModel(); //['order_id', 'order_token', 'total', 'cash_received', 'user_id', 'branch_id', 'status', 'created_at'];


        $user_info = $user->where('token', $token)->first();

        //check if the current_transaction_tbl that is equal to your_id data if theres no data, create new order 

        //order_token=tokenmaker
        //total=null
        //cash_received=null
        //user_id=$user_info['user_id]
        //branch_id=$user_info['branch_id']
        //status='processing'

        //in $UPCAndQuantity look if there is a @ sign as splitter the first portion is the upc and the second is the quantity 
        //example 12392367834@3 (which means the upc is 12392367834 and 3 is the quantity)

        //get the product that that has the same 'upc' field as the $upc
        //then get the 'price' field of that product and multiplity it to $quantity

        //reminder if the product[quantity] is less than to $quantity show that the stock are not enough 


    }
    public function SubmitCurrentTransaction($cash_received, $order_token)
    {

        $audit = new AuditModel(); //['audit_id', 'product_id', 'old_quantity', 'quantity', 'type', 'exp_date', 'user_id', 'branch_id', 'created_at'];

        $CurrentTransaction = new CurrentTransactionModel(); //['current_transaction_id', 'order_id', 'product_id', 'quantity', 'sub_total', 'user_id', 'branch_id', 'created_at'];
        $order = new OrderModel(); //['order_id', 'order_token', 'total', 'cash_received', 'user_id', 'branch_id', 'status', 'created_at'];
        //transfer the data that is equal to $order_token of the current_transaction_tbl to the sales_tbl and audit_tbl

        //get the order token and get its id and update the value of its status to "complete"


        //sales_tbl
        //order_id =order_id
        //product_id=product_id
        //quantity=quantity
        //subtotal=subtotal
        //user_id=user_id
        //branch_id=branch_id

        //audit_tbl
        //product_id=product_id
        //check if there is already a existing data  that is equal to $product id if there is already data get the latest one
        //example:$old_quantity = $the_latest['old_quantity'] 
        //example:$quantity = $the_latest['quantity'] 

        //audit_tbl
        //old_quantity = $old_quantity
        //type= 'outbound'
        //exp_date=null
        //user_id=user_id
        //branch_id=branch_id

        //after successful transafering make sure to delete all the data that is equal to $order_token

        //
    }
    private function clearData($token)
    {
        $CurrentTransaction = new CurrentTransactionModel();
        $user = new UserModel();


        $user_info = $user->where('token', $token)->first();


        //clear the data in quanity 
    }
    private function tokenMaker($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}
