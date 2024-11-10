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


class CurrentTransactionController extends ResourceController
{

    private $totalAmount = 0;

    public function index()
    {
        $main = new CurrentTransactionModel();
        $data = $main->findAll();
        return $this->respond($data);
    }

    public function BranchOrderView()
    {
        $audit = new AuditModel();
        $user = new UserModel();
        $prod = new ProductModel();
        $token = $this->request->getVar('token');
        $order_token = $this->request->getVar('order_token');
        $profile = $user->where('token', $token)->first();

        $OrderViewing = $audit->where('token_code', $order_token)
            ->where('branch_id', $profile['branch_id'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Loop through each entry in $OrderViewing to replace product_id with product_name
        foreach ($OrderViewing as &$order) {
            $product = $prod->find($order['product_id']);
            $order['generic_name'] = $product['generic_name'];
            // Optionally, you can unset the product_id if it's no longer needed
            // unset($order['product_id']);
        }

        return $this->respond($OrderViewing);
    }

    public function SalesTransactionList()
    {
        $order = new OrderModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');

        // Validate token presence
        if (!$token) {
            return $this->fail('Token is required', 400);
        }

        // Retrieve user profile
        $profile = $user->where('token', $token)->first();

        // Validate user profile
        if (!$profile) {
            return $this->failNotFound('User not found or invalid token');
        }

        $branchId = $profile['branch_id'];
        log_message('info', "Branch ID: $branchId"); // Log the branch ID for debugging

        // Fetch all orders for the branch
        $orders = $order
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Filter out non-completed orders
        $CompleteOrders = array_filter($orders, function ($order) {
            return $order['status'] === 'completed';
        });

        log_message('info', 'Complete Orders Count: ' . count($CompleteOrders));

        if (empty($CompleteOrders)) {
            return $this->respond(['message' => 'No completed orders found'], 200);
        }

        return $this->respond($CompleteOrders);
    }



    public function TransactionTotalAmount($order_token)
    {
        $currentTransaction = new CurrentTransactionModel();
        $transactions = $currentTransaction->where('order_token', $order_token)->findAll();
        $TotalAll = 0;
        foreach ($transactions as $transaction) {
            // Transfer data to audit table
            $product_ID = $transaction['product_id'];
            $product = new ProductModel();
            $product_info = $product->find($transaction['product_id']);
            $TotalAll += $product_info['SRP'] * $transaction['quantity'];
        }
        // Format the total amount with comma as thousand separator and two decimal places
        $formattedTotal = number_format($TotalAll, 2, '.', ',');
        $this->totalAmount = $formattedTotal;
        return $this->respond(['msg' => $formattedTotal]);
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
            $transaction['generic_name'] = $transaction['quantity'] . '_' . $product_info['generic_name'] . '(' . $product_info['brand_name'] . ')';
            // Calculate the total for each transaction item
            $transaction['SRP'] = $product_info['SRP'];
            $subtotal = $product_info['SRP'] * $transaction['quantity'];
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
        date_default_timezone_set('Asia/Manila');
        $created_at = date('Y-m-d H:i:s');

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
                'earnings' => 0,
                'cash_received' => 0,
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
                'created_at' => $created_at,
                'discount_type' => 0,
            ];
            // Check the result of the insert operation
            $insert_result = $order->insert($new_order_data);


            if (!$insert_result) {
                return $this->respond(['msg' => ',Error inserting new order', 'error' => true]);
            }
            $existing_order = $order->where('order_token', $order_token)->first();
        }

        // Check if "@" is present in UPCAndQuantity
        if (strpos($UPCAndQuantity, '@') !== false) {
            // Split UPCAndQuantity to get UPC and Quantity
            list($UPC, $quantity) = explode('@', $UPCAndQuantity);
            $prod_info = $product->where(['upc' => $UPC, 'branch_id' => $user_info['branch_id']])->first();
            // Find the product with the given UPC and branch_id
            $quantity = (int)$quantity; // Explicitly cast to integer
            $prod_info_quantity = (int)$prod_info['quantity']; // Explicitly cast to integer
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
                    return $this->respond(['msg' => 'Remove product: ' . $prod_info['generic_name'], 'error' => true]);
                } else {
                    return $this->respond(['msg' => 'Wrong quantity input', 'error' => true]);
                }
            }
        }



        // Check if the product is associated with the current branch
        if (!$prod_info) {
            // Product not found
            return $this->respond(['msg' => 'Product not found', 'error' => true]);
        }

        if ($prod_info['branch_id'] != $user_info['branch_id']) {
            return $this->respond(['msg' => 'Product not available in your branch', 'error' => true]);
        }

        if ($quantity > $prod_info['quantity']) {
            return $this->respond([
                'msg' => 'Insufficient stock',
                'error' => true
            ]);
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
                return $this->respond(['msg' => '2 Our stocks are just too low', 'error' => true]);
            }
            $currentTransaction->update($existing_transaction['current_transaction_id'], ['quantity' => $updated_quantity]);
        } else {
            // If the product does not exist, insert into current_transaction
            $current_transaction_data = [
                'order_token' => $order_token,
                'product_id' => $prod_info['product_id'],
                'quantity' => $quantity,
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
                'created_at' => $created_at,
            ];
            //if returned this is not running
            $insertCT = $currentTransaction->insert($current_transaction_data);
            if (!$insertCT) {
                return $this->respond(['msg' => 'Error inserting into current transaction', 'error' => true]);
            }
        }

        return $this->respond(['msg' => 'data inserted successfully']);
    }






    public function SubmitCurrentTransaction()
    {
        $token = $this->request->getVar('token');
        $cash_received = $this->request->getVar('cash_received');
        $order_token = $this->request->getVar('order_token');
        $discount = $this->request->getVar('discount'); // value is 20 so the meaning is 20% discount

        $audit = new AuditModel(); //['audit_id', 'product_id', 'old_quantity', 'quantity', 'type', 'exp_date', 'user_id', 'branch_id', 'created_at'];
        $currentTransaction = new CurrentTransactionModel(); //['current_transaction_id', 'order_id', 'product_id', 'quantity','user_id', 'branch_id', 'created_at'];
        $orderModel = new OrderModel(); //['order_id', 'order_token', 'total', 'cash_received', 'user_id', 'branch_id', 'status', 'created_at'];
        $notification = new NotificationModel();
        $user = new UserModel();
        $user_info = $user->where('token', $token)->first();

        // Fetch all transactions related to the order_token
        $transactions = $currentTransaction->where('order_token', $order_token)->findAll();

        // Initialize variables to calculate total and exact change
        $total = 0;
        $earnings = 0;
        // Calculate total amount and earnings for the transaction, considering the discount
        foreach ($transactions as $transaction) {
            $product = new ProductModel();
            $product_info = $product->find($transaction['product_id']);


            // Calculate the discounted price for the product

            $discounted_price = $product_info['SRP'] * (1 - ($discount / 100));

            // Update total and earnings based on the discounted price
            $total += $discounted_price * $transaction['quantity'];
            // $earnings += $product_info['profit'] * $transaction['quantity'];

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
            if ($existingAudit_type == 'received') {
                $existing_old_quantity_1 = $existing_old_quantity + $exist_quantity;
            } elseif ($existingAudit_type == 'sold') {
                $existing_old_quantity_1 = $existing_old_quantity - $exist_quantity;
            }

            $audit_data = [
                'product_id' => $transaction['product_id'],
                'token_code' => $order_token,
                'old_quantity' => $existing_old_quantity_1,
                'quantity' => $transaction['quantity'],
                // 'earnings' => $transaction['earnings'],
                'type' => 'sold', // Assuming this is a sold transfer
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Update product quantity in the product table based on the sold transaction
            $new_quantity = $product_info['quantity'] - $transaction['quantity'];

            $audit->insert($audit_data);
            $product->update($transaction['product_id'], ['quantity' => $new_quantity]);
            $prod_details = $product->where(['product_id' => $transaction['product_id']])->first();
            if ($new_quantity == 0) {
                $product->where('product_id', $transaction['product_id'])
                    ->set(['status' => 'out of stock'])
                    ->update();
            }
            if ($new_quantity <= $prod_details['notif_quantity_trigger']) {
                $prod_details = $product->where(['product_id' => $transaction['product_id']])->first();
                $notif = [
                    'event_type' => 'product',
                    'related_id' => $transaction['product_id'],
                    'branch_id' =>  $transaction['branch_id'],
                    "title" => "Low Stock",
                    'message' => $prod_details['generic_name'] . '(' . $prod_details['brand_name'] . ')' . 'got low stocks',
                ];
                $notification->insert($notif);
            }
        }

        // Check if the total amount needs to be adjusted based on the cash received
        if ($cash_received < $total) {
            $need = number_format($total - $cash_received, 2);
            return $this->respond(['msg' => 'Insufficient cash received need ₱' . $need . ' more', 'error' => true]);
        } else {
            // Calculate the exact change
            $exact_change = number_format($cash_received - $total, 2);

            // Update order status, total, and cash_received
            $orderModel->where('order_token', $order_token)
                ->set([
                    'status' => 'completed',
                    'total' => $total,
                    'earnings' => $earnings,
                    'cash_received' => $cash_received
                ])->update();

            // Clear current transactions for the order
            $currentTransaction->where('order_token', $order_token)->delete();

            // Format amounts for response
            $cash_received = number_format($cash_received, 2);
            $total = number_format($total, 2);

            return $this->respond(['msg' => 'Transaction submitted successfully', 'ReceivedCash' => $cash_received, 'Total' => $total, 'exact_change' => $exact_change]);
        }
    }
    public function SubmitCurrentTransactionAdmin()
    {
        $token = $this->request->getVar('token');
        $cash_received = $this->request->getVar('cash_received');
        $order_token = $this->request->getVar('order_token');
        $discount = $this->request->getVar('discount');
        $audit = new AuditModel();
        $currentTransaction = new CurrentTransactionModel();
        $orderModel = new OrderModel();
        $notification = new NotificationModel();
        $user = new UserModel();
        $user_info = $user->where('token', $token)->first();

        // Fetch all transactions related to the order_token
        $transactions = $currentTransaction->where('order_token', $order_token)->findAll();

        // Initialize variables to calculate total and exact change
        $total = 0;
        $earnings = 0;
        // Calculate total amount and earnings for the transaction, considering the discount
        foreach ($transactions as $transaction) {
            $product = new ProductModel();
            $product_info = $product->find($transaction['product_id']);

            // Update total and earnings based on the discounted price
            $total += $product_info['SRP'] * $transaction['quantity'];

            // $earnings += $product_info['profit'] * $transaction['quantity']; //TODO

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
            if ($existingAudit_type == 'received') {
                $existing_old_quantity_1 = $existing_old_quantity + $exist_quantity;
            } elseif ($existingAudit_type == 'sold') {
                $existing_old_quantity_1 = $existing_old_quantity - $exist_quantity;
            }

            $audit_data = [
                'product_id' => $transaction['product_id'],
                'token_code' => $order_token,
                'old_quantity' => $existing_old_quantity_1,
                'quantity' => $transaction['quantity'],
                'earnings' => 0,
                'type' => 'sold', // Assuming this is a sold transfer
                'user_id' => $user_info['user_id'],
                'branch_id' => $user_info['branch_id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Update product quantity in the product table based on the sold transaction
            $new_quantity = $product_info['quantity'] - $transaction['quantity'];

            $audit->insert($audit_data);
            $product->update($transaction['product_id'], ['quantity' => $new_quantity]);

            $prod_details = $product->where(['product_id' => $transaction['product_id']])->first();

            if ($new_quantity == 0) {
                $product->where('product_id', $transaction['product_id'])
                    ->set(['status' => 'out of stock'])
                    ->update();
                $prod_details = $product->where(['product_id' => $transaction['product_id']])->first();
                $notif = [
                    'event_type' => 'product',
                    'related_id' => $transaction['product_id'],
                    'branch_id' =>  $transaction['branch_id'],
                    "title" => "Out of stock",
                    'message' => $prod_details['generic_name'] . 'is out of stock',
                ];
                $notification->insert($notif);
            } else if ($new_quantity <= $prod_details['notif_quantity_trigger']) {
                $product->where('product_id', $transaction['product_id'])
                    ->set(['status' => 'low stock'])
                    ->update();
                $notif = [
                    'event_type' => 'product',
                    'related_id' => $transaction['product_id'],
                    'branch_id' =>  $transaction['branch_id'],
                    "title" => "Low Stock",
                    'message' => $prod_details['generic_name'] . ' got low stocks',
                ];
                $notification->insert($notif);
            } else {
                $product->where('product_id', $transaction['product_id'])
                    ->set(['status' => 'available'])
                    ->update();
            }
        }


        // Update order status, total, and cash_received
        $orderModel->where('order_token', $order_token)
            ->set([
                'status' => 'completed',
                'total' => 0,
                'earnings' => 0,
                'cash_received' => 0,
            ])->update();

        // Clear current transactions for the order
        $currentTransaction->where('order_token', $order_token)->delete();

        // Format amounts for response
        $cash_received = number_format($cash_received, 2);
        $total = number_format($total, 2);

        return $this->respond(['msg' => 'Transaction submitted successfully']);
    }
    public function ClearCurrentTransaction()
    {
        $user = new UserModel();
        $currentTransaction = new CurrentTransactionModel();

        // Retrieve token and order_token from the request
        $token = $this->request->getVar('token');
        $order_token = $this->request->getVar('order_token');

        // Get user info using the token
        $user_info = $user->where('token', $token)->first();

        // Fetch all transactions related to the order_token
        $transactions = $currentTransaction->where('order_token', $order_token)->findAll();

        // Delete all transactions related to the order_token
        if (!empty($transactions)) {
            $deldata = $currentTransaction->where('order_token', $order_token)->delete();
            if ($deldata) {
                return $this->respond(['msg' => 'clear successfully']);
            } else {
                return $this->respond(['msg' => 'clear unsuccessful', 'error' => true]);
            }
        }
    }


    private function tokenMaker($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

    public function ExpirationChecker()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $audit = new AuditModel();

        $token = $this->request->getVar('token');
        $product_id = $this->request->getVar('product_id');

        // Fetch user and product info
        $user_info = $user->where('token', $token)->first();
        $product_info = $prod->where('product_id', $product_id)->first();

        if (!$user_info || !$product_info) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid token or product ID']);
        }

        // Get audits for the product, separated by type and ordered by expiry for received
        $audit_received = $audit->where('product_id', $product_id)->where('type', 'received')->orderBy('exp_date', 'ASC')->findAll();
        $audit_sold = $audit->where('product_id', $product_id)->where('type', 'sold')->findAll();

        // Calculate total quantity sold
        $total_sold = array_sum(array_column($audit_sold, 'quantity'));

        // Track remaining stock for closest expiry calculation
        $remaining_sold = $total_sold;
        $closest_expiry = null;
        $closest_expiry_id = null;

        foreach ($audit_received as $entry) {
            $batch_qty = $entry['quantity'];
            $exp_date = $entry['exp_date'];
            $audit_id = $entry['audit_id'];

            // Deduct sold quantity from this batch if any remaining
            if ($remaining_sold > 0) {
                $allocated_qty = min($batch_qty, $remaining_sold);
                $remaining_sold -= $allocated_qty;
                $batch_qty -= $allocated_qty;
            }

            // If batch still has remaining quantity, set it as closest expiry
            if ($batch_qty > 0) {
                $closest_expiry = $exp_date;
                $closest_expiry_id = $audit_id;
                break;  // Stop as we've found the closest expiry with stock remaining
            }
        }

        return $this->respond([
            'status' => 'success',
            'closest_expiry' => $closest_expiry,
            'closest_expiry_id' => $closest_expiry_id,
            'remaining_sold' => $remaining_sold
        ]);
    }
}
