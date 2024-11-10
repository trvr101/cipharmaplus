<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;
use App\Models\UserModel;



class ProductController extends ResourceController
{
    public function index()
    {
        $main = new ProductModel();
        $data = $main->where('status !=', 'deleted')->findAll();
        return $this->respond($data);
    }

    // All
    public function AdminProductList()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $data = $prod->where('status !=', 'deleted')
            ->findAll();

        return $this->respond($data);
    }
    public function AdminProductListFilter()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $filter_product_name = $this->request->getVar('filter_product_name');
        $filter_description = $this->request->getVar('filter_description');
        $filter_category = $this->request->getVar('filter_category');
        $filter_status = $this->request->getVar('filter_status');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $query = $prod->where('status !=', 'deleted')
            ->where('branch_id', $profile['branch_id']);

        // Add product_name filter if provided and not null
        if ($filter_product_name !== null) {
            $filterValues = array_values($filter_product_name); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('product_name', $filterValues);
            } else {
                // Use regular where for single product_name
                $query->where('product_name', $filterValues);
            }
        }
        if ($filter_description !== null) {
            $filterValues = array_values($filter_description); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('description', $filterValues);
            } else {
                // Use regular where for single description
                $query->where('description', $filterValues);
            }
        }
        if ($filter_category !== null) {
            $filterValues = array_values($filter_category); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('category', $filterValues);
            } else {
                // Use regular where for single category
                $query->where('category', $filterValues);
            }
        }
        if ($filter_status !== null) {
            $filterValues = array_values($filter_status); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('status', $filterValues);
            } else {
                // Use regular where for single status
                $query->where('status', $filterValues);
            }
        }

        $data = $query->findAll();

        return $this->respond($data);
    }
    public function BranchProductList()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $data = $prod->where('status !=', 'deleted')
            ->where('branch_id',  $profile['branch_id'])
            ->orderBy('created_at', 'desc')
            ->findAll();

        return $this->respond($data);
    }
    //for table
    public function BranchProductListFilter()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $filter_product_name = $this->request->getVar('filter_product_name');
        $filter_description = $this->request->getVar('filter_description');
        $filter_category = $this->request->getVar('filter_category');
        $filter_status = $this->request->getVar('filter_status');

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            return $this->fail('User not found', 404);
        }

        // Fetch products based on the branch_id
        $query = $prod->where('status !=', 'deleted')
            ->where('branch_id', $profile['branch_id']);

        // Add product_name filter if provided and not null
        if ($filter_product_name !== null) {
            $filterValues = array_values($filter_product_name); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('product_name', $filterValues);
            } else {
                // Use regular where for single product_name
                $query->where('product_name', $filterValues);
            }
        }
        if ($filter_description !== null) {
            $filterValues = array_values($filter_description); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('description', $filterValues);
            } else {
                // Use regular where for single description
                $query->where('description', $filterValues);
            }
        }
        if ($filter_category !== null) {
            $filterValues = array_values($filter_category); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('category', $filterValues);
            } else {
                // Use regular where for single category
                $query->where('category', $filterValues);
            }
        }
        if ($filter_status !== null) {
            $filterValues = array_values($filter_status); // Extract values from associative array

            if (is_array($filterValues)) {
                // Use whereIn for array of categories
                $query->whereIn('status', $filterValues);
            } else {
                // Use regular where for single status
                $query->where('status', $filterValues);
            }
        }
        $query->orderBy('created_at', 'desc');
        $data = $query->findAll();

        return $this->respond($data);
    }



    public function BranchProduct($token)
    {
        $main = new ProductModel();
        $user = new UserModel();

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();
        $category = $this->request->getVar('category'); // Get the category array

        if (!$profile) {
            // Handle the case where the user with the specified token is not found
            return $this->fail('User not found', 404);
        }

        // Get the branch_id of the user
        $branchId = $profile['branch_id'];

        // Check if the 'value' key in the 'category' array exists and is not empty
        $categoryValue = isset($category['value']) ? $category['value'] : null;

        if (!empty($categoryValue)) {
            // Fetch products based on branch_id and category value
            $data = $main->where('status !=', 'deleted')
                ->where('branch_id', $branchId)
                ->where('category', $categoryValue)
                ->orderBy('generic_name', 'ASC')
                ->findAll();
        } else {
            // Fetch products based only on branch_id
            $data = $main->where('status !=', 'deleted')
                ->where('branch_id', $branchId)
                ->orderBy('generic_name', 'ASC')
                ->findAll();
        }

        return $this->respond($data);
    }

    public function AddProd()
    {
        $main = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Check if prod_upc is provided, if null generate a unique 11-digit UPC
        $upc = $this->request->getVar('UPC');
        if (empty($upc)) {
            do {
                // Generate a random 11-digit number
                $upc = mt_rand(10000000000, 99999999999);
                // Check if the generated UPC already exists in the database
                $existingUPC = $main->where('upc', $upc)->first();
            } while ($existingUPC); // Keep generating until a unique UPC is found
        }

        // Get input values for generic_name, brand_name, and dosage_form
        $generic_name = $this->request->getVar('generic_name');
        $brand_name = $this->request->getVar('brand_name');
        $notif_quantity_trigger = $this->request->getVar('notif_quantity_trigger');
        $notif_expiry_trigger = $this->request->getVar('notif_expiry_trigger');
        $dosage_form = $this->request->getVar('dosage_form');
        $batch_num = $this->request->getVar('batch_num');
        // Check if a product with the same generic_name, brand_name, and dosage_form already exists
        $existingProduct = $main->where('generic_name', $generic_name)
            ->where('brand_name', $brand_name)
            ->where('dosage_form', $dosage_form)
            ->where('batch_num', $batch_num)
            ->first();

        if ($existingProduct) {
            return $this->respond(['msg' => 'Product already exists', 'error' => true]);
        }

        // Proceed with product insertion if no duplicate is found
        $data = [
            'user_id' => $profile['user_id'],
            'upc' => $upc,
            'generic_name' => $generic_name,
            'brand_name' => $brand_name,
            'dosage_form' => $dosage_form,
            'batch_num' => $batch_num,
            'SRP' => $this->request->getVar('SRP'),
            'unit_price' => $this->request->getVar('unit_price'),
            'branch_id' => $profile['branch_id'],
            'category' => $this->request->getVar('category'),
            'status' => 'out of stock',
            'notif_quantity_trigger' => $notif_quantity_trigger,
            'notif_expiry_trigger' => $notif_expiry_trigger,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $result = $main->save($data);

        if ($result) {
            return $this->respond(['msg' => $generic_name . ' is added successfully']);
        } else {
            return $this->respond(['msg' => 'Adding new product unsuccessful', 'error' => true]);
        }
    }



    public function ItemCategoryList()
    {
        $main = new ProductCategoryModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function itemInfo()
    {
        $prod = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('product_id');
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        //get the product based on product_id
        $profile = $user->where('token', $token)->first();
    }
    public function countUniqueItems()
    {
        $main = new ProductModel();
        $data = $main->findAll();

        $uniqueItems = [];

        foreach ($data as $prod) {
            $prodName = $prod['product_name'];
            $description = $prod['description'] ?? ''; // If strength is null, set it to an empty string

            // Combine item name and strength to create a unique identifier
            $uniqueIdentifier = $prodName . '|' . $description;

            // Check if the unique identifier already exists in the array
            if (!in_array($uniqueIdentifier, $uniqueItems)) {
                // If not, add it to the array
                $uniqueItems[] = $uniqueIdentifier;
            }
        }

        // Count the unique items
        $count = count($uniqueItems);

        return $this->respond(['count' => $count]);
    }
    public function countBranchUniqueItems()
    {
        $main = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        // Fetch products directly filtered by branch_id
        $data = $main
            ->where('branch_id', $profile['branch_id'])
            ->findAll();

        // Create an array to store unique items
        $uniqueItems = [];

        foreach ($data as $prod) {
            $prodName = $prod['generic_name'];
            $brandName = $prod['brand_name'];
            $description = $prod['dosage_form'] ?? ''; // If description is null, set it to an empty string

            // Combine item name and description to create a unique identifier
            $uniqueIdentifier = $prodName . '|' . $brandName . '|' . $description;

            // Use the unique identifier as the array key for faster checks
            $uniqueItems[$uniqueIdentifier] = true;
        }

        // Count the unique items
        $count = count($uniqueItems);

        // Count the newly added items (assuming you have a timestamp field like 'created_at')
        $newlyAdded = $main
            ->where('branch_id', $profile['branch_id'])
            ->where('created_at >= CURDATE()') // Change the condition based on your timestamp field
            ->countAllResults();
        return $this->respond(['count' => $count, 'newly_added' => $newlyAdded]);
    }


    public function branchInventory($branchId)
    {
        $productModel = new ProductModel();

        // Assuming 'products' is your table name and 'branch_id' is the column name
        $products = $productModel->where('branch_id', $branchId)->findAll();

        return $this->respond($products);
    }
    //TODO
    public function ProdUpdate()
    {
        // Retrieve token from the request
        $token = $this->request->getVar('token');

        // Check if token is provided
        if (!$token) {
            return $this->respond(['msg' => 'Token not provided', 'error' => true]);
        }

        // Retrieve user based on the token
        $user = new UserModel();
        $profile = $user->where('token', $token)->first();

        // Check if user profile exists
        if (!$profile) {
            return $this->respond(['msg' => 'User not found', 'error' => true]);
        }

        // Retrieve product ID from the request
        $product_id = $this->request->getVar('product_id');

        // Check if product ID is provided
        if (!$product_id) {
            return $this->respond(['msg' => 'Product ID not provided', 'error' => true]);
        }

        // Retrieve product based on the product ID
        $prod = new ProductModel();
        $product = $prod->find($product_id);

        // Check if product exists
        if (!$product) {
            return $this->respond(['msg' => 'Product not found', 'error' => true]);
        }

        // Assuming you want to update these fields from request data
        $data = [];

        // Retrieve and update each field if present in the request
        $upc = $this->request->getVar('upc');
        if ($upc != null) {
            $data['upc'] = $upc;
        }

        $generic_name = $this->request->getVar('generic_name');
        if ($generic_name != null) {
            $data['generic_name'] = $generic_name;
        }

        $brand_name = $this->request->getVar('brand_name');
        if ($brand_name != null) {
            $data['brand_name'] = $brand_name;
        }

        $dosage = $this->request->getVar('dosage_form');
        if ($dosage != null) {
            $data['dosage_form'] = $dosage;
        }

        $batch_num = $this->request->getVar('batch_num');
        if ($batch_num != null) {
            $data['batch_num'] = $batch_num;
        }

        $SRP = $this->request->getVar('SRP');
        if ($SRP != null) {
            $data['SRP'] = $SRP;
        }

        $unit_price = $this->request->getVar('unit_price');
        if ($unit_price != null) {
            $data['unit_price'] = $unit_price;
        }

        $category = $this->request->getVar('category');
        if ($category != null) {
            $data['category'] = $category;
        }

        $status = $this->request->getVar('status');
        if ($status != null) {
            $data['status'] = $status;
        }

        $notif_quantity_trigger = $this->request->getVar('notif_quantity_trigger');
        if ($notif_quantity_trigger != null) {
            $data['notif_quantity_trigger'] = $notif_quantity_trigger;
        }

        $notif_expiry_trigger = $this->request->getVar('notif_expiry_trigger');
        if ($notif_expiry_trigger != null) {
            $data['notif_expiry_trigger'] = $notif_expiry_trigger;
        }

        // Update the product
        $updating = $prod->update($product_id, $data);

        if ($updating) {
            return $this->respond(['msg' => 'Product updated successfully']);
        } else {
            return $this->respond(['msg' => 'Failed to update product', 'error' => true]);
        }
    }
    public function deleteprod()
    {
        $token = $this->request->getVar('token');
        $prodModel = new ProductModel();
        $userModel = new UserModel();

        // Check if token is provided
        if (!$token) {
            return $this->respond(['msg' => 'Token not provided', 'error' => true]);
        }

        // Retrieve user based on the token
        $profile = $userModel->where('token', $token)->first();

        if (!$profile) {
            return $this->respond(['msg' => 'Invalid token', 'error' => true]);
        }

        $product_id = $this->request->getVar('product_id');
        $product = $prodModel->find($product_id);

        // Check if the product exists
        if (!$product) {
            return $this->respond(['msg' => 'Product not found', 'error' => true]);
        }

        // Check if user has permission to delete the product
        if (
            $profile['branch_id'] == $product['branch_id'] &&
            ($profile['user_role'] == 'admin' || $profile['user_role'] == 'branch_admin')
        ) {

            // Delete the product
            $prodModel->delete($product_id);
            return $this->respond(['msg' => 'Product deleted successfully']);
        } else {
            return $this->respond(['msg' => 'You do not have permission to delete this product', 'error' => true]);
        }
    }
}
