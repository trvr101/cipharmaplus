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
use App\Models\NotificationModel;

class AdminController extends ResourceController
{
    public function index() {}
    //TODO: Done
    public function AdminInventoryFilter()
    {

        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }
        $data = $prod
            ->where('branch_id',  $profile['branch_id'])
            ->orderBy('created_at', 'desc')
            ->findAll();

        return $this->respond($data);
    }
    public function ProdInfo()
    {

        $product = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $product_id = $this->request->getVar('product_id');
        $profile = $user->where('token', $token)->first();
        $prod_info = $product->where(['product_id' => $product_id, 'branch_id' => $profile['branch_id']])->first();
        if (!$profile) {
            return $this->fail('User not found', 404);
        }
        $data = $product
            ->where('branch_id',  $profile['branch_id'])
            ->orderBy('created_at', 'desc')
            ->findAll();

        return $this->respond($prod_info);
    }

    //TODO
    public function AdminInventoryTable()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $filter_generic_name = $this->request->getVar('filter_generic_name');
        $filter_category_value = $this->request->getVar('filter_category[value]');
        $filter_status_value = $this->request->getVar('filter_status[value]');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $query = $prod->where('branch_id', $profile['branch_id']);

        // Filter by generic name if provided
        if (!empty($filter_generic_name)) {
            $query->like('generic_name', $filter_generic_name, 'after');
        }

        // Filter by category if provided
        if (!empty($filter_category_value)) {
            $query->where('category', $filter_category_value);
        }

        // Filter by status if provided
        if (!empty($filter_status_value)) {
            $query->where('status', $filter_status_value);
        }

        // Order the results by created_at in descending order
        $query->orderBy('created_at', 'desc');

        // Get the resulting data
        $data = $query->findAll();

        return $this->respond($data);
    }
    public function AdminInventoryTablePOS()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $filter_generic_name = $this->request->getVar('filter_generic_name');
        $filter_category_value = $this->request->getVar('filter_category[value]');
        $filter_status_value = $this->request->getVar('filter_status[value]');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $query = $prod->where('branch_id', $profile['branch_id']);

        // Add condition to filter products with quantity > 0
        $query->where('quantity >', 0);

        // Filter by generic name if provided
        if (!empty($filter_generic_name)) {
            $query->like('generic_name', $filter_generic_name, 'after');
        }

        // Filter by category if provided
        if (!empty($filter_category_value)) {
            $query->where('category', $filter_category_value);
        }

        // Filter by status if provided
        if (!empty($filter_status_value)) {
            $query->where('status', $filter_status_value);
        }

        // Order the results by created_at in descending order
        $query->orderBy('created_at', 'desc');

        // Get the resulting data
        $data = $query->findAll();

        return $this->respond($data);
    }





    public function AdminSalesTable()
    {
        $order = new OrderModel(); // ['order_id', 'order_token', 'status', 'total', 'earnings', 'cash_received', 'user_id', 'branch_id',  'created_at', 'discount_type'];
        $user = new UserModel(); // ['user_id', 'first_name', 'last_name', 'email', 'user_password', 'phone', 'user_role', 'branch_id', 'status', 'token', 'created_at'];
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Start building the query
        $query = $order->where('branch_id', $profile['branch_id'])->where('status', 'completed');


        // Order the results by created_at in descending order
        $query->orderBy('created_at', 'DESC');

        // Get the resulting data
        $salesData = $query->findAll();

        // Loop through the sales data and replace user_id with email
        foreach ($salesData as &$data) {
            $userData = $user->find($data['user_id']); // Fetch user data based on user_id
            if ($userData) {
                $data['user_email'] = $userData['email']; // Add email to the result
            } else {
                $data['user_email'] = 'Unknown'; // Fallback if no user found
            }
            unset($data['user_id']); // Optionally remove user_id if not needed
        }

        return $this->respond($salesData);
    }

    public function AdminSalesFilter()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $branchId = $profile['branch_id'];

        $CompleteOrders = $order
            ->where('branch_id', $branchId)
            ->where('status', "completed")
            ->orderBy('created_at', 'DESC')
            ->findAll();
        return $this->respond($CompleteOrders);
    }




    public function AdminProductViewTable() {}
    public function AdminProductViewFilter() {}




    public function AdminOrderViewTable() {}

    public function CurrentTransactionListMAIN($token, $order_token)
    {
        $currentTransaction = new CurrentTransactionModel();
        $user = new UserModel();

        // Retrieve user information based on the provided token
        $user_info = $user->where('token', $token)->first();
        // Retrieve transactions with the given order token and user's branch_id
        $transactions = $currentTransaction
            ->where('order_token', $order_token)
            ->where('branch_id', $user_info['branch_id'])
            ->findAll();

        // Check if transactions are found
        if (empty($transactions)) {
            return $this->respond(['msg' => 'No transactions found for the given order token']);
        }


        // Calculate total based on product prices and quantities
        $total = 0;
        foreach ($transactions as &$transaction) {
            $product_ID = $transaction['product_id'];
            $product = new ProductModel();
            $product_info = $product->find($transaction['product_id']);

            // Check if the product exists
            if (!$product_info) {
                return $this->respond(['msg' => 'Error fetching product information']);
            }

            // Add product name to the transaction
            $transaction['generic_name'] =  $product_info['generic_name'];
            $transaction['dosage_form'] = $product_info['dosage_form'];

            // Calculate the total for each transaction item
        }

        // Return the result with total
        return $this->respond([
            'transactions' => $transactions,
            'total' => $total,
        ]);
    }
}
