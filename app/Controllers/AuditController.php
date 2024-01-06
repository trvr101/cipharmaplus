<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\AuditModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\SalesModel;


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
                if ($audit['type'] == 'inbound') {
                    $audit['total'] = $audit['old_quantity'] + $audit['quantity'];
                } elseif ($audit['type'] == 'outbound') {
                    $audit['total'] = $audit['old_quantity'] - $audit['quantity'];
                }
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

    public function addQuantity($token, $product_id)
    {
        // Create instances of the AuditModel and ProductModel
        $main = new AuditModel();
        $user = new UserModel();
        $product = new ProductModel();

        $user_info = $user->where('token', $token)->first();
        $prod_info = $product->where('product_id', $product_id)->first();
        if (!$user_info) {
            return $this->respond(['msg' => 'user not exist']);
        }
        if ($user_info['branch_id'] == $prod_info['branch_id'] || $user_info['user_role'] == 'admin') {
            // Find the latest audit record for the given product_id
            $existingAudit = $main->where('product_id', $product_id)
                ->orderBy('created_at', 'DESC')
                ->first();

            // Initialize variables for old_quantity and quantity
            $exist_old_quantity = 0;
            $exist_quantity = 0;

            // Calculate the new old_quantity based on the existing audit record
            if ($existingAudit) {
                $exist_old_quantity = $existingAudit['old_quantity'];
                $exist_quantity = $existingAudit['quantity'];
            }

            // Prepare the data for the new audit record
            $data = [
                'product_id'   => $product_id,
                'old_quantity' => $exist_old_quantity + $exist_quantity,
                'quantity'     => $this->request->getVar('quantity'),
                'type'         => 'inbound',
                'exp_date'     => $this->request->getVar('date'),
                'user_id'      => $user_info['user_id'],
                'branch_id'    => $user_info['branch_id'],
                'created_at'   => date('Y-m-d H:i:s'),
            ];

            // Save the new audit record
            $result = $main->save($data);

            if ($result) {
                // Update the product quantity
                $total_quantity = $data['old_quantity'] + $data['quantity'];
                $product->where('product_id', $product_id)
                    ->set(['quantity' => $total_quantity])
                    ->update();

                return $this->respond(['msg' => 'okay']);
            } else {
                return $this->respond(['msg' => 'failed']);
            }
        } else {
            return $this->respond(['msg' => 'your not able to access this page']);
        }
    }
}
