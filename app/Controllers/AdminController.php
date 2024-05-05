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
    public function index()
    {
    }
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
    public function AdminInventoryTable()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
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
        $order = new OrderModel();
        $user = new UserModel();
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

        return $this->respond($salesData);
    }


    public function AdminSalesFilter()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        $CompleteOrders =
            $order
            ->where('branch_id', $profile['branch_id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();
        return $this->respond($CompleteOrders);
    }




    public function AdminProductViewTable()
    {
    }
    public function AdminProductViewFilter()
    {
    }




    public function AdminOrderViewTable()
    {
    }
}
