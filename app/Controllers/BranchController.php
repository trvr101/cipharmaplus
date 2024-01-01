<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchModel;
use App\Models\ProductModel;

class BranchController extends ResourceController
{
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
