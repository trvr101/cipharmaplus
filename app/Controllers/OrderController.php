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
    public function TransactionToday()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $today = date('Y-m-d');

        $OrdersToday = $order
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >=', $today)
            ->where('status', 'completed')
            ->findAll();

        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $OrdersYesterday = $order
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >=', $yesterday . ' 00:00:00')
            ->where('created_at <', $yesterday . ' 23:59:59')
            ->where('status', 'completed')
            ->findAll();
        $OrdersToday = count($OrdersToday);
        $OrdersYesterday = count($OrdersYesterday);
        $percentageDifference = (($OrdersToday - $OrdersYesterday) / $OrdersYesterday) * 100;
        $percentageDifference = number_format($percentageDifference, 2, '.', ',');
        return $this->respond([
            'orders_today' => $OrdersToday,
            'orders_yesterday' => $OrdersYesterday,
            'percentage' => $percentageDifference
        ]);
    }

    public function EarningsPerWeek()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Get orders for the last 7 days
        $lastSevenDaysStart = date('Y-m-d', strtotime('-7 days'));
        $lastSevenDaysOrders = $order
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >=', $lastSevenDaysStart)
            ->where('status', 'completed')
            ->findAll();
        $last7daysEarnings = array_sum(array_column($lastSevenDaysOrders, 'earnings'));

        // Get orders for the previous week (day 8 to day 14)
        $previousWeekStart = date('Y-m-d', strtotime('-14 days'));
        $previousWeekEnd = date('Y-m-d', strtotime('-7 days'));
        $lastWeekOrders = $order
            ->where('branch_id', $profile['branch_id'])
            ->where('status', 'completed')
            ->where('created_at >=', $previousWeekStart)
            ->where('created_at <', $previousWeekEnd)
            ->findAll();
        $lastWeekEarnings = array_sum(array_column($lastWeekOrders, 'earnings'));

        // Calculate the percentage difference
        $percentageDifference = 0;
        if ($lastWeekEarnings != 0) {
            $percentageDifference = (($last7daysEarnings - $lastWeekEarnings) / $lastWeekEarnings) * 100;
        }
        $last7daysEarnings = number_format($last7daysEarnings, 2, '.', ',');
        $lastWeekEarnings = number_format($lastWeekEarnings, 2, '.', ',');
        $percentageDifference = number_format($percentageDifference, 2);

        return $this->respond([
            'last7daysEarnings' => $last7daysEarnings,
            'lastWeekEarnings' => $lastWeekEarnings,
            'percentageDifference' => $percentageDifference,
        ]);
    }
    public function AverageOrderValuePerWeek()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Get AOV for the last 7 days
        $lastSevenDaysStart = date('Y-m-d', strtotime('-7 days'));
        $lastSevenDaysOrders = $order
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >=', $lastSevenDaysStart)
            ->where('status', 'completed')
            ->findAll();
        $totalLast7DaysOrders = count($lastSevenDaysOrders);
        $totalLast7DaysAmount = array_sum(array_column($lastSevenDaysOrders, 'total'));
        $AOVLast7Days = ($totalLast7DaysOrders > 0) ? $totalLast7DaysAmount / $totalLast7DaysOrders : 0;

        // Get AOV for the previous week (day 8 to day 14)
        $previousWeekStart = date('Y-m-d', strtotime('-14 days'));
        $previousWeekEnd = date('Y-m-d', strtotime('-7 days'));
        $lastWeekOrders = $order
            ->where('branch_id', $profile['branch_id'])
            ->where('status', 'completed')
            ->where('created_at >=', $previousWeekStart)
            ->where('created_at <', $previousWeekEnd)
            ->findAll();
        $totalLastWeekOrders = count($lastWeekOrders);
        $totalLastWeekAmount = array_sum(array_column($lastWeekOrders, 'total'));
        $AOVLastWeek = ($totalLastWeekOrders > 0) ? $totalLastWeekAmount / $totalLastWeekOrders : 0;

        // Calculate the percentage difference in AOV
        $percentageDifferenceAOV = 0;
        if ($AOVLastWeek != 0) {
            $percentageDifferenceAOV = number_format((($AOVLast7Days - $AOVLastWeek) / $AOVLastWeek) * 100, 1);
        }

        $AOVLast7Days = number_format($AOVLast7Days, 2);
        $AOVLastWeek = number_format($AOVLastWeek, 2);
        return $this->respond([
            'AOVLast7Days' => $AOVLast7Days,
            'AOVLastWeek' => $AOVLastWeek,
            'percentageDifferenceAOV' => $percentageDifferenceAOV,
        ]);
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
