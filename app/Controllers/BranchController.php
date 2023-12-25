<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchModel;

class BranchController extends ResourceController
{
    use ResponseTrait;

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
