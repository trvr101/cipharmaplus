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


class CurrentTransactionController extends ResourceController
{
    public function index()
    {
        $main = new CurrentTransactionModel();
        $data = $main->findAll();
        return $this->respond($data);
    }

    public function CurrentTransactionList($token, $order_token)
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
            $transaction['product_name'] = $transaction['quantity'] . '_' . $product_info['product_name'];
            // Calculate the total for each transaction item
            $transaction['price'] = $product_info['price'];
            $subtotal = $product_info['price'] * $transaction['quantity'];
            $transaction['subtotal'] = $subtotal;

            $total += $subtotal;
        }

        // Return the result with total
        return $this->respond([
            'transactions' => $transactions,
            'total' => $total,
        ]);
    }





    public function AddItemToCurrentTransaction($token, $order_token)
    {
        $UPCAndQuantity = $this->request->getVar('UPCAndQuantity');
        $user = new UserModel();
        $product = new ProductModel();
        $order = new OrderModel();
        $currentTransaction = new CurrentTransactionModel(); // Assuming you have a model for current_transaction

        // Get user information based on the provided token
        $user_info = $user->where('token', $token)->first();

        // Check if the order with the given order_token already exists
        $existing_order = $order->where('order_token', $order_token)->first();

        if (!$existing_order) {
            // If the order doesn't exist, create a new order
            $new_order_data = [
                'order_token' => $order_token,
                'status' => 'processing',
                'total' => 0,
                'cash_received' => 0,
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
            ];

            // Check the result of the insert operation
            $insert_result = $order->insert($new_order_data);

            if (!$insert_result) {
                // Error in inserting new order
                return $this->respond(['msg' => 'Error inserting new order', 'error' => true]);
            }

            // Retrieve the newly created order
            $existing_order = $order->where('order_token', $order_token)->first();
        }
        // Check if "@" is present in UPCAndQuantity
        if (strpos($UPCAndQuantity, '@') !== false) {
            // Split UPCAndQuantity to get UPC and Quantity
            list($UPC, $quantity) = explode('@', $UPCAndQuantity);
            $prod_info = $product->where(['upc' => $UPC, 'branch_id' => $user_info['branch_id']])->first();

            if ($quantity == null) {
                $quantity = 1;
            }
            if ($quantity == 0) {
                $isDeleted = $currentTransaction
                    ->where([
                        'product_id' => $prod_info['product_id'],
                        'order_token' => $order_token,
                    ])
                    ->delete();
                if ($isDeleted) {
                    //delete the existing_transaction on $currentTransaction
                    return $this->respond(['msg' => 'Remove product: ' . $prod_info['product_name'], 'error' => true]);
                } else {
                    return $this->respond(['msg' => 'Wrong quantity input', 'error' => true]);
                }
            }
        }

        // Find the product with the given UPC and branch_id
        $prod_info = $product->where(['upc' => $UPC, 'branch_id' => $user_info['branch_id']])->first();

        // Check if the product is associated with the current branch
        if (!$prod_info) {
            // Product not found
            return $this->respond(['msg' => 'Product not found', 'error' => true]);
        }

        if ($prod_info['branch_id'] != $user_info['branch_id']) {
            return $this->respond(['msg' => 'Product not available in your branch', 'error' => true]);
        }
        if ($quantity > $prod_info['quantity']) {
            return $this->respond(['msg' => 'Our stocks are just ' . $prod_info['product_name'] . ': ' . $prod_info['quantity'] . ' could not handle ' . $quantity, 'error' => true]);
        }
        // Check if the product already exists in currentTransaction
        $existing_transaction = $currentTransaction
            ->where([
                'product_id' => $prod_info['product_id'],
                'order_token' => $order_token,
            ])
            ->first();

        if ($existing_transaction) {
            // If the product already exists, update the quantity

            $updated_quantity =  $quantity;
            if ($updated_quantity > $prod_info['quantity']) {
                return $this->respond(['msg' => 'Our stocks are just ' . $prod_info['product_name'] . ': ' . $prod_info['quantity'] . ' could not handle ' . $updated_quantity, 'error' => true]);
            }
            $currentTransaction->update($existing_transaction['current_transaction_id'], ['quantity' => $updated_quantity]);
        } else {
            // If the product does not exist, insert into current_transaction
            $current_transaction_data = [
                'order_token' => $order_token,
                'product_id' => $prod_info['product_id'],
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
                'quantity' => $quantity,
            ];

            $currentTransaction->insert($current_transaction_data);
        }

        return $this->respond(['msg' => 'data inserted successfully']);
    }
    public function SubmitCurrentTransaction($cash_received, $order_token, $token)
    {
        $audit = new AuditModel(); //['audit_id', 'product_id', 'old_quantity', 'quantity', 'type', 'exp_date', 'user_id', 'branch_id', 'created_at'];
        $currentTransaction = new CurrentTransactionModel(); //['current_transaction_id', 'order_id', 'product_id', 'quantity','user_id', 'branch_id', 'created_at'];
        $orderModel = new OrderModel(); //['order_id', 'order_token', 'total', 'cash_received', 'user_id', 'branch_id', 'status', 'created_at'];

        $user = new UserModel();
        $user_info = $user->where('token', $token)->first();

        // Fetch all transactions related to the order_token
        $transactions = $currentTransaction->where('order_token', $order_token)->findAll();

        // Initialize variables to calculate total and exact change
        $total = 0;

        foreach ($transactions as $transaction) {
            // Transfer data to audit table
            $product_ID = $transaction['product_id'];
            $existingAudit = $audit->where('product_id', $product_ID)->orderBy('created_at', 'DESC')->first();

            // You may need to adapt the following lines based on your specific requirements
            $existing_old_quantity = 0;
            $exist_quantity = 0;
            $existing_old_quantity = $existingAudit['old_quantity']; // Adapt as needed
            $exist_quantity = $existingAudit['quantity']; // Adapt as needed
            $existingAudit_type = $existingAudit['type'];

            // Adjust existing_old_quantity based on the existingAudit_type
            if ($existingAudit_type == 'inbound') {
                $existing_old_quantity_1 = $existing_old_quantity + $exist_quantity;
            } elseif ($existingAudit_type == 'outbound') {
                $existing_old_quantity_1 = $existing_old_quantity - $exist_quantity;
            }

            $audit_data = [
                'product_id' => $transaction['product_id'],
                'token_code' => $order_token,
                'old_quantity' => $existing_old_quantity_1,
                'quantity' => $transaction['quantity'],
                'type' => 'outbound', // Assuming this is an outbound transfer
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $product = new ProductModel();
            $product_info = $product->find($transaction['product_id']);
            $total += $product_info['price'] * $transaction['quantity'];

            // Update total based on product price and quantity

            // Update product quantity in the product table based on the outbound transaction
            $new_quantity = $product_info['quantity'] - $transaction['quantity'];
            $product->update($transaction['product_id'], ['quantity' => $new_quantity]);
            if ($cash_received < $total) {
                $need = number_format($total - $cash_received, 2);
                return $this->respond(['msg' => 'Insufficient cash received need ₱' . $need . ' more', 'error' => true]);
            } else {
                $audit->insert($audit_data);
            }
        }

        // Calculate exact change




        // Update order status, total, and cash_received
        $orderModel->where('order_token', $order_token)
            ->set([
                'status' => 'completed',
                'total' => $total,
                'cash_received' => $cash_received
            ])->update();
        // Clear current transactions for the order
        $currentTransaction->where('order_token', $order_token)->delete();
        $exact_change = number_format($cash_received - $total, 2);
        $cash_received = number_format($cash_received, 2);
        $total = number_format($total, 2);
        return $this->respond(['msg' => 'Transaction submitted successfully', 'ReceivedCash' => $cash_received, 'Total' => $total, 'exact_change' => $exact_change]);
    }



    private function clearData($token)
    {
        $CurrentTransaction = new CurrentTransactionModel();
        $user = new UserModel();


        $user_info = $user->where('token', $token)->first();



        //clear the data in quanity 
    }
    private function tokenMaker($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}