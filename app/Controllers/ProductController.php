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

        $data = $query->findAll();

        return $this->respond($data);
    }



    public function BranchProduct($token)
    {
        $main = new ProductModel();
        $user = new UserModel();

        // Get the user that has the same token as $token
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            // Handle the case where the user with the specified token is not found
            return $this->fail('User not found', 404);
        }

        // Get the branch_id of the user
        $branchId = $profile['branch_id'];

        // Fetch products based on the branch_id
        $data = $main->where('status !=', 'deleted')
            ->where('branch_id', $branchId)
            ->findAll();

        return $this->respond($data);
    }
    public function AddProd()
    {
        $main = new ProductModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        $data = [
            'user_id' => $profile['user_id'],
            'upc' => $this->request->getVar('prod_upc'),
            'product_name' => $this->request->getVar('prod_name'),
            'description' => $this->request->getVar('prod_desc'),
            'original_price' => $this->request->getVar('original_price'),
            'profit' => $this->request->getVar('profit'),
            'price' =>  $this->request->getVar('original_price') + $this->request->getVar('profit'),
            'branch_id' => $profile['branch_id'],
            'category' => $this->request->getVar('category_name'),
            'status' => 'available',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $result = $main->save($data);

        if ($result) {
            return $this->respond(['msg' => 'okay']);
        } else {
            return $this->respond(['msg' => 'failed']);
        }
    }

    public function ItemCategoryList()
    {
        $main = new ProductCategoryModel();
        $data = $main->findAll();
        return $this->respond($data);
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
            $prodName = $prod['product_name'];
            $description = $prod['description'] ?? ''; // If description is null, set it to an empty string

            // Combine item name and description to create a unique identifier
            $uniqueIdentifier = $prodName . '|' . $description;

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
}
