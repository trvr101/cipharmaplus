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
    //TODO
    public function AdminInventoryTable()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $filter_generic_name = $this->request->getVar('filter_generic_name');
        $filter_category = $this->request->getVar('filter_category');
        $filter_status = $this->request->getVar('filter_status');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $query = $prod
            ->where('branch_id', $profile['branch_id']);

        // Filter by generic name if provided
        if (!empty($filter_generic_name)) {
            $query->where('generic_name LIKE', $filter_generic_name . '%');
        }


        // Filter by category if provided
        if (!empty($filter_category)) {
            $categories = [];
            foreach ($filter_category as $category) {
                $categories[] = $category['value'];
            }
            $query->whereIn('category', $categories);
        }

        // Filter by status if provided
        if (!empty($filter_status)) {
            $statuses = [];
            foreach ($filter_status as $status) {
                $statuses[] = $status['value'];
            }
            $query->whereIn('status', $statuses);
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
        $date_range = $this->request->getVar('date_range');
        $profile = $user->where('token', $token)->first();

        // Start building the query
        $query = $order->where('branch_id', $profile['branch_id']);

        // Filter by date range if provided
        if (!empty($date_range)) {
            // Split the date range by '/'
            $dates = explode('/', $date_range);

            // Ensure both start and end dates are present
            if (count($dates) == 3) {
                $start_date = $dates[0] . '-' . $dates[1] . '-' . $dates[2];
                $end_date = $dates[0] . '-' . $dates[1] . '-' . $dates[2];

                // Assuming 'created_at' is the field representing the order date
                $query->where('DATE(created_at)', $start_date);
                $query->where('DATE(created_at)', $end_date);
            } else {
                // If only one date is provided, you can adjust the query accordingly
                $query->where('DATE(created_at)', $date_range);
            }
        }

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

        // Validate token presence
        if (!$token) {
            return $this->fail('Token is required', 400);
        }

        // Retrieve user profile
        $profile = $user->where('token', $token)->first();

        // Validate user profile
        if (!$profile) {
            return $this->failNotFound('User not found or invalid token');
        }

        $branchId = $profile['branch_id'];
        log_message('info', "Branch ID: $branchId"); // Log the branch ID for debugging

        $CompleteOrders = $order
            ->where('branch_id', $branchId)
            ->where('status', "completed")
            ->orderBy('created_at', 'DESC')
            ->findAll();

        log_message('info', 'Complete Orders Query: ' . $order->getLastQuery());

        if (empty($CompleteOrders)) {
            return $this->respond(['message' => 'No completed orders found'], 200);
        }

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
