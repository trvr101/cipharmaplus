<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\AuditModel;
use App\Models\UserModel;
use App\Models\ProductModel;

class AuditController extends ResourceController
{
    public function index()
    {
        $main = new AuditModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function ProductAudit($token, $product_id)
    {
        $main = new AuditModel();
        $prod = new ProductModel();
        $user = new UserModel();

        // Step 1: Get the user that has the equal token to $token and get its branch_id
        $user_info = $user->where('token', $token)->first();
        if (!$user_info) {
            return "User not found or error retrieving user information.";
        }
        $user_branch_id = $user_info['branch_id'];

        // Step 2: Get the product that has the same product_id as $product_id
        $product_info = $prod->where('product_id', $product_id)->first();
        if (!$product_info) {
            return "Product not found or error retrieving product information.";
        }

        // Step 3: Check if the branch_id of user and prod are the same or user is admin
        if ($user_branch_id == $product_info['branch_id'] || $user_info['user_role'] == 'admin') {
            // Step 4: Get all the audits that have the same product_id as $product_id, ordered by the latest first
            $audits = $main->where('product_id', $product_id)->orderBy('created_at', 'DESC')->findAll();

            // Add the product_name and total to each audit record
            foreach ($audits as &$audit) {
                $audit['product_name'] = $product_info['product_name'];
                $audit['total'] = $audit['old_quantity'] + $audit['quantity'];
            }

            // Now $audits contains all the audit information for the specified product_id in the same branch as the user, ordered by the latest first,
            // and each audit record includes the product_name and total
            // You can process and return this information as needed
            return $this->respond($audits);
        } else {
            // Handle the case where the user and product are not in the same branch
            return "User and product are not in the same branch.";
        }
    }





    public function addQuantity($product_id)
    {
        // Create a new instance of the AuditModel
        $main = new AuditModel();

        // Find the latest audit record for the given product_id
        $existingAudit = $main->where('product_id', $product_id)
            ->orderBy('created_at', 'DESC')
            ->first();

        // Initialize variables for old_quantity and quantity

        // Calculate the new old_quantity based on the existing audit record
        if ($existingAudit) {
            $exist_old_quantity = $existingAudit['old_quantity'];
            $exist_quantity = $existingAudit['quantity'];
            $sum = $exist_old_quantity + $exist_quantity;
            $exist_old_quantity = $sum;
        } else {
            $sum = 0;
        }

        // Prepare the data for the new audit record
        $data = [
            'product_id'   => $product_id,
            'old_quantity' => $sum,
            'quantity'     => $this->request->getVar('quantity'),
            'type'         => 'inbound',
            'exp_date'     => $this->request->getVar('exp_date'),
            'user_id'      => $this->request->getVar('user_id'),
            'branch_id'    => $this->request->getVar('branch_id'),
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        // Save the new audit record
        $result = $main->save($data);

        if ($result) {
            return $this->respond(['msg' => 'okay']);
        } else {
            return $this->respond(['msg' => 'failed']);
        }
    }
}
