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
    //Top Selling Products START HERE
    public function TopSellingProductPerWeek()
    {
        $audit = new AuditModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $lastSevenDaysStart = date('Y-m-d', strtotime('-7 days'));
        $previousWeekStart = date('Y-m-d', strtotime('-14 days'));

        // Get data for the current week
        $ProductSoldThisWeek = $this->getProductSoldData($audit, $profile['branch_id'], $lastSevenDaysStart, 'outbound');

        // Get data for the previous week
        $ProductSoldLastWeek = $this->getProductSoldData($audit, $profile['branch_id'], $previousWeekStart, 'outbound');

        // Organize and count the sum of product quantities sold for both weeks
        $productQuantitiesThisWeek = $this->organizeProductQuantities($ProductSoldThisWeek);
        $productQuantitiesLastWeek = $this->organizeProductQuantities($ProductSoldLastWeek);

        // Sort product quantities in descending order for both weeks
        arsort($productQuantitiesThisWeek);
        arsort($productQuantitiesLastWeek);
        // Get the top 3 selling products for both weeks
        $topSellingProductsThisWeek = array_slice($productQuantitiesThisWeek, 0, 3, true);

        // Fetch additional details for the top 3 selling products for both weeks
        $topSellingProductDetailsThisWeek = $this->fetchProductDetails($topSellingProductsThisWeek);
        $topSellingProductDetailsLastWeek = $this->fetchProductDetails($productQuantitiesLastWeek);

        // Compare rankings and calculate the rank difference
        $rankDifference = $this->calculateRankDifference($topSellingProductsThisWeek, $productQuantitiesLastWeek);

        // Include the rank difference and indicator in the response
        foreach ($topSellingProductDetailsThisWeek as &$productDetails) {
            $productId = $productDetails['product_id'];
            $productDetails['rank_difference'] = $rankDifference[$productId] ?? null;
            $productDetails['rank_indicator'] = $this->calculateRankIndicator($rankDifference[$productId]);
        }

        return $this->respond([
            'TopSellingProductsThisWeek' => $topSellingProductDetailsThisWeek,
            'TopSellingProductsLastWeek' => $topSellingProductDetailsLastWeek,
        ]);
    }

    private function calculateRankIndicator($rankDifference)
    {
        if ($rankDifference > 0) {
            return 'up';
        } elseif ($rankDifference < 0) {
            return 'down';
        } else {
            return 'no_change';
        }
    }

    private function calculateRankDifference($currentWeek, $previousWeek)
    {
        $rankDifference = [];
        $currentRank = 1;
        foreach ($currentWeek as $productId => $quantity) {
            $previousRank = array_search($productId, array_keys($previousWeek)) + 1;
            $rankDifference[$productId] = $previousRank - $currentRank;
            $currentRank++;
        }

        return $rankDifference;
    }

    private function fetchProductDetails($productQuantities)
    {
        $productDetails = [];
        foreach ($productQuantities as $productId => $quantity) {
            // Replace this with your code to fetch product details based on the product ID
            $productModel = new ProductModel();
            $details = $productModel->find($productId);

            $details['quantity_sold'] = $quantity;
            $productDetails[] = $details;
        }

        return $productDetails;
    }
    private function getProductSoldData($audit, $branchId, $startDate, $type)
    {
        return $audit
            ->where('branch_id', $branchId)
            ->where('created_at >=', $startDate)
            ->where('type', $type)
            ->findAll();
    }

    private function organizeProductQuantities($productSoldData)
    {
        $productQuantities = [];
        foreach ($productSoldData as $auditEntry) {
            $productId = $auditEntry['product_id'];
            $quantity = intval($auditEntry['quantity']);

            if (isset($productQuantities[$productId])) {
                $productQuantities[$productId] += $quantity;
            } else {
                $productQuantities[$productId] = $quantity;
            }
        }

        return $productQuantities;
    }
    //Top Selling Products END HERE
    public function ExpirationAlertPerProduct()
    {
        $user = new UserModel();
        $audit = new AuditModel();
        $token = $this->request->getVar('token');
        $product_id = $this->request->getVar('product_id');
        $profile = $user->where('token', $token)->first();

        //Get all outbound transactions and sum up the quantity
        $OutboundTotalQuantity = $audit
            ->where('product_id', $product_id)
            ->where('type', 'outbound')
            ->where('branch_id', $profile['branch_id'])
            ->findAll();
        $InboundTotalQuantity = $audit
            ->where('product_id', $product_id)
            ->where('type', 'inbound')
            ->where('branch_id', $profile['branch_id'])
            ->findAll();
        $outboundSum = array_sum(array_column($OutboundTotalQuantity, 'quantity'));
        $existingProduct = [];
        $inboundSum = 0;
        foreach ($InboundTotalQuantity as $inbound) {
            $inboundSum += $inbound['quantity'];
            if ($outboundSum <= $inboundSum) {
                if (count($existingProduct) == 0) {
                    $cutoff = $inboundSum - $outboundSum;
                    $inbound['quantity'] = $cutoff;
                }
                $existingProduct[] = $inbound;
            }
        }
        $closestExpirationDate = null;
        $closestExpirationDateData = null;
        foreach ($existingProduct as $product) {
            if ($product['exp_date'] < $closestExpirationDate || $closestExpirationDate == null) {
                $closestExpirationDate = $product['exp_date'];
                $closestExpirationDateData = $product;
            }
        }
        //loop the existing products and check the closest exp_date
        $response = [
            'existingProduct' => $existingProduct,
            'closestExpirationDate' => $closestExpirationDate,
            'closestExpirationDateData' => $closestExpirationDateData,
        ];
        return $this->respond($response);
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
            //$audits = $main->where('product_id', $product_id)->orderBy('created_at', 'DESC')->findAll();
            $audits = $main->where('product_id', $product_id)->findAll();

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
            if ($existingAudit) { // if there is a record in existing run this
                $existing_old_quantity = $existingAudit['old_quantity'];
                $exist_quantity = $existingAudit['quantity'];
                $existingAudit_type = $existingAudit['type'];

                // Adjust existing_old_quantity based on the existingAudit_type
                if ($existingAudit_type == 'inbound') {
                    $existing_old_quantity_1 = $existing_old_quantity + $exist_quantity;
                } elseif ($existingAudit_type == 'outbound') {
                    $existing_old_quantity_1 = $existing_old_quantity - $exist_quantity;
                }

                // Prepare the data for the new audit record
                $data = [
                    'product_id'   => $product_id,
                    'old_quantity' => $existing_old_quantity_1,
                    'quantity'     => $this->request->getVar('quantity'),
                    'type'         => 'inbound',
                    'exp_date'     => $this->request->getVar('date'),
                    'user_id'      => $user_info['user_id'],
                    'branch_id'    => $user_info['branch_id'],
                    'created_at'   => date('Y-m-d H:i:s'),
                ];
            } else { // if there is no existingaudit record yet run this
                $data = [
                    'product_id'   => $product_id,
                    'old_quantity' => 0,
                    'quantity'     => $this->request->getVar('quantity'),
                    'type'         => 'inbound',
                    'exp_date'     => $this->request->getVar('date'),
                    'user_id'      => $user_info['user_id'],
                    'branch_id'    => $user_info['branch_id'],
                    'created_at'   => date('Y-m-d H:i:s'),
                ];
            }

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
            return $this->respond(['msg' => 'you are not able to access this page']);
        }
    }
}
