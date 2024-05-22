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


class BranchAdminController extends ResourceController
{
    public function index()
    {
        //
    }
    public function Graph()
    {
        $orderModel = new OrderModel();
        $userModel = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $userModel->where('token', $token)->first();

        $lastSevenDaysStart = date('Y-m-d', strtotime('-7 days'));

        // Get all completed orders from the last 7 days for the user's branch
        $orders = $orderModel
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >=', $lastSevenDaysStart)
            ->where('status', 'completed')
            ->findAll();

        // Initialize an array to store the counts for each day
        $dailyOrderCounts = [
            'Mon' => 0,
            'Tue' => 0,
            'Wed' => 0,
            'Thu' => 0,
            'Fri' => 0,
            'Sat' => 0,
            'Sun' => 0
        ];

        // Populate the array with the counts of orders for each day
        foreach ($orders as $order) {
            $dayOfWeek = date('D', strtotime($order['created_at']));
            $dailyOrderCounts[$dayOfWeek]++;
        }

        // Get the current day of the week (0 for Sunday, 1 for Monday, etc.)
        $currentDayIndex = date('w');

        // Reorder the array to start from the current day
        $orderedDailyCounts = array();
        for ($i = 0; $i < 7; $i++) {
            $dayIndex = ($currentDayIndex + $i) % 7;
            $dayName = date('D', strtotime("Sunday +{$dayIndex} days"));
            $orderedDailyCounts[$dayName] = $dailyOrderCounts[$dayName];
        }

        // Return the response in the desired format
        return $this->respond($orderedDailyCounts);
    }
}
