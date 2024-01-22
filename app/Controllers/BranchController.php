<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchModel;
use App\Models\ProductModel;
use App\Models\UserModel;

class BranchController extends ResourceController
{
    public function InvitationalCode()
    {
        $branch = new BranchModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $BranchData = $branch->where('branch_id', $profile['branch_id'])->first();
        return $this->respond($BranchData);
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

                $magnitude = $this->request->getVar('magnitude');
                if ($magnitude != null) {
                    $data['magnitude'] = $magnitude;
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
