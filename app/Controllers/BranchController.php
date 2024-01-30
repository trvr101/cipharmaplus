<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchModel;
use App\Models\ProductModel;
use App\Models\UserModel;
use App\Models\AuditModel;

class BranchController extends ResourceController
{
    public function TotalBranchWorker()
    {
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Count total workers
        $branchWorkers = $user
            ->where('branch_id', $profile['branch_id'])
            ->findAll();
        $branchWorkerCount = count($branchWorkers);

        // Count new workers today
        $today = date('Y-m-d');
        $NewWorkers = $user
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >=', $today)
            ->findAll();
        $NewWorkerCount = count($NewWorkers);

        return $this->respond([
            'totalWorkers' => $branchWorkerCount,
            'NewWorkersToday' => $NewWorkerCount,
        ]);
    }

    public function BranchSalesPerWeek()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $audit = new AuditModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Fetch audits for the specific branch
        $branchAudits = $audit->where('branch_id', $profile['branch_id'])->where('type', 'outbound')->findAll();

        // Group audits by week
        $weeklySales = [];
        foreach ($branchAudits as $audit) {
            $weekStartDate = date('Y-m-d', strtotime('monday this week', strtotime($audit['created_at'])));
            $weeklySales[$weekStartDate][] = $audit['quantity'];
        }

        // Calculate total quantity sold for each week
        $totalQuantityByWeek = [];
        foreach ($weeklySales as $weekStartDate => $sales) {
            $totalQuantityByWeek[$weekStartDate] = array_sum($sales);
        }

        // Find the percentage difference from the last week
        $weeks = array_keys($totalQuantityByWeek);
        rsort($weeks); // Sort weeks in descending order
        $currentWeek = reset($weeks);
        $previousWeek = next($weeks);

        $percentageDifference = 0;
        if (isset($totalQuantityByWeek[$previousWeek]) && $totalQuantityByWeek[$previousWeek] !== 0) {
            $percentageDifference = (($totalQuantityByWeek[$currentWeek] - $totalQuantityByWeek[$previousWeek]) / $totalQuantityByWeek[$previousWeek]) * 100;
        }

        return $this->respond([
            'currentWeek' => $currentWeek,
            'previousWeek' => $previousWeek,
            'percentageDifference' => $percentageDifference,
            'totalQuantityByWeek' => $totalQuantityByWeek,
        ]);
    }
    public function SalesPredictionPerWeek()
    {
        $audit = new AuditModel();
        $token = $this->request->getVar('token');

        // Assuming you have a user model to get the user's branch
        $user = new UserModel();
        $profile = $user->where('token', $token)->first();

        // Fetch audits for the specific branch
        $branchAudits = $audit->where('branch_id', $profile['branch_id'])->where('type', 'inbound')->findAll();

        // Extract dates and corresponding quantities
        $dates = [];
        $quantities = [];
        foreach ($branchAudits as $audit) {
            $dates[] = $audit['created_at'];
            $quantities[] = $audit['quantity'];
        }

        // Calculate the moving average
        $windowSize = 3; // You can adjust this based on your data and desired prediction accuracy
        $totalQuantities = count($quantities);

        if ($totalQuantities > $windowSize) {
            // Calculate the average for the last $windowSize entries
            $lastWindowQuantities = array_slice($quantities, -$windowSize);
            $average = array_sum($lastWindowQuantities) / $windowSize;

            // Predict the next sales based on the average
            $prediction = $average;

            return $this->respond([
                'prediction' => $prediction,
                'lastWindowQuantities' => $lastWindowQuantities,
                'average' => $average,
            ]);
        } else {
            // Not enough data for prediction
            return $this->respond(['error' => 'Not enough data for prediction']);
        }
    }
    public function SalesPredictionPerDay()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $audit = new AuditModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Fetch audits for the specific branch
        $branchAudits = $audit->where('branch_id', $profile['branch_id'])->findAll();

        // Group audits by day
        $dailySales = [];
        foreach ($branchAudits as $audit) {
            $day = date('Y-m-d', strtotime($audit['created_at']));
            $dailySales[$day][] = $audit['quantity'];
        }

        // Identify the last two days
        $days = array_keys($dailySales);
        rsort($days);
        $lastTwoDays = array_slice($days, 0, 2);

        // Calculate total quantity sold for the last two days
        $totalQuantityLastTwoDays = 0;
        foreach ($lastTwoDays as $day) {
            $totalQuantityLastTwoDays += array_sum($dailySales[$day]);
        }

        // Calculate average sales for the last two days
        $averageSales = count($lastTwoDays) > 0 ? $totalQuantityLastTwoDays / count($lastTwoDays) : 0;

        // Predict sales for the next day
        $nextDay = date('Y-m-d', strtotime('tomorrow'));
        $predictedSales = round($averageSales);

        return $this->respond([
            'lastTwoDays' => $lastTwoDays,
            'predictedSales' => $predictedSales,
            'averageSales' => $averageSales,
            'totalQuantityLastTwoDays' => $totalQuantityLastTwoDays,
        ]);
    }




    public function RegenerateInvitationCode()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $type = $this->request->getVar('type');
        $profile = $user->where('token', $token)->first();
        $BranchData = $branch->where('branch_id', $profile['branch_id'])->first();
        $newCode = $this->generateCode(20);
        if ($type == 'BA') {
            $updating = $branch->update($BranchData['branch_id'], ['BA_invite_code' => 'BA.' . $newCode]);
            if ($updating) {
                return $this->respond(['msg' =>  'New branch admin Invitational code generated']);
            }
        } elseif ($type == 'CS') {
            $updating = $branch->update($BranchData['branch_id'], ['CS_invite_code' => 'CS.' . $newCode]);
            if ($updating) {
                return $this->respond(['msg' =>  'New Cashier Invitational code generated']);
            }
        }
    }
    public function IsOpenForInvitation()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $BranchData = $branch->where('branch_id', $profile['branch_id'])->first();
        if ($BranchData['is_open_for_invitation']) {
            return $this->respond(['msg' => true]);
        } else {
            return $this->respond(['msg' => false]);
        }
    }

    public function toggleInvitation()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $BranchData = $branch->where('branch_id', $profile['branch_id'])->first();

        $updating = $branch->update($BranchData['branch_id'], ['is_open_for_invitation' => !$BranchData['is_open_for_invitation']]);
        if ($updating) {
            if (!$BranchData['is_open_for_invitation']) {
                return $this->respond(['msg' => $BranchData['branch_name'] . ' is now open for invitations']);
            } else {
                return $this->respond(['msg' => $BranchData['branch_name'] . ' is not open for invitations', 'error' => true]);
            }
        } else {
            return $this->respond(['msg' => 'toggle unsuccessfully', 'error' => true]);
        }
    }

    public function BranchInfo()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $BranchData = $branch->where('branch_id', $profile['branch_id'])->first();
        return $this->respond($BranchData);
    }
    public function UpdateBranchInfo()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        if ($profile && $profile['user_role'] == 'branch_admin') {
            $BranchData = $branch->where('branch_id', $profile['branch_id'])->first();
            if ($BranchData) {
                $data = [];


                // Check if each field is not null before adding it to the $data array
                $branch_name = $this->request->getVar('branch_name');
                if ($branch_name != null) {
                    $data['branch_name'] = $branch_name;
                }

                $latitude = $this->request->getVar('latitude');
                if ($latitude != null) {
                    $data['latitude'] = $latitude;
                }

                $longitude = $this->request->getVar('longitude');
                if ($longitude != null) {
                    $data['longitude'] = $longitude;
                }

                $barangay = $this->request->getVar('barangay');
                if ($barangay != null) {
                    $data['barangay'] = $barangay;
                }

                $opening_time = $this->request->getVar('opening_time');
                if ($opening_time != null) {
                    $data['opening_time'] = $opening_time;
                }
                $closing_time = $this->request->getVar('closing_time');
                if ($closing_time != null) {
                    $data['closing_time'] = $closing_time;
                }
                $contact_number = $this->request->getVar('contact_number');
                if ($contact_number != null) {
                    $data['contact_number'] = $contact_number;
                }
                $email = $this->request->getVar('email');
                if ($email != null) {
                    $data['email'] = $email;
                }
                $email = $this->request->getVar('email');
                if ($email != null) {
                    $data['email'] = $email;
                }
                $updating = $branch->update($BranchData['branch_id'], $data);
                if ($updating) {
                    return $this->respond(['msg' => 'Updated Successfully']);
                } else {
                    return $this->respond(['msg' => 'Update Unsuccessfully', 'error' => true]);
                }
            }
        }
        return $this->respond($BranchData);
    }
    public function countStocksPerBranch()
    {
        $branchModel = new BranchModel();
        $productModel = new ProductModel();

        // Get all the different branches
        $branches = $branchModel->findAll();

        // Calculate and store the total stocks for each branch
        $stocksPerBranch = [];
        $overallSum = 0;
        foreach ($branches as $branch) {
            $products = $productModel->where('branch_id', $branch['branch_id'])->findAll();
            $totalStocks = array_sum(array_column($products, 'quantity'));
            $overallSum += $totalStocks;
            $stocksPerBranch[] = [
                'branch_id' => $branch['branch_id'],
                'branch_name' => $branch['branch_name'],
                'total_stocks' => $totalStocks,
            ];
        }

        return $this->respond([
            'stocks_per_branch' => $stocksPerBranch,
            'overall_sum' => $overallSum,
        ]);
    }

    public function index()
    {
        $main = new BranchModel();
        $data = $main->findAll();
        return $this->respond($data);
    }

    public function addBranch()
    {
        $model = new BranchModel();

        // Get branch name from the request
        $branch_name = $this->request->getVar('branch_name');

        // Generate invite codes
        $csInviteCode = 'CS.' . $this->generateCode(50);
        $baInviteCode = 'BA.' . $this->generateCode(50);

        // Prepare data for insertion
        $data = [
            'branch_name' => $branch_name,
            'CS_invite_code' => $csInviteCode,
            'BA_invite_code' => $baInviteCode,
        ];

        // Insert data into the database
        $result = $model->insert($data);

        if ($result) {
            // Respond with a success message
            return $this->respondCreated(['message' => 'Branch added successfully']);
        } else {
            // Log the error for debugging purposes
            log_message('error', 'Error adding branch: ' . $model->errors());

            // Respond with a server error message
            return $this->failServerError('Error adding branch');
        }
    }

    private function generateCode($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}
