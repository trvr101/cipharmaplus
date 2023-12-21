<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;

class ProductController extends ResourceController
{
    public function index()
    {
        //
        $main = new ProductModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    // Your CodeIgniter 4 controller method
    public function AddProd()
    {
        $main = new ProductModel();

        // Debugging: Check the received data
        var_dump($this->request->getVar());

        $data = [
            'user_id' => $this->request->getVar('my_user_id'),
            'product_name' => $this->request->getVar('prod_name'),
            'description' => $this->request->getVar('prod_desc'),
            'price' => $this->request->getVar('prod_price'),
            'branch_id' => $this->request->getVar('prod_branch_id'),
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
    public function countBranchUniqueItems($branchId)
    {
        $main = new ProductModel();
        $data = $main->findAll();

        $uniqueItems = [];

        foreach ($data as $prod) {
            // Check if the product belongs to the specified branch
            if ($prod['branch_id'] == $branchId) {
                $prodName = $prod['product_name'];
                $description = $prod['description'] ?? ''; // If description is null, set it to an empty string

                // Combine item name and description to create a unique identifier
                $uniqueIdentifier = $prodName . '|' . $description;

                // Use the unique identifier as the array key for faster checks
                if (!isset($uniqueItems[$uniqueIdentifier])) {
                    // If not, add it to the array
                    $uniqueItems[$uniqueIdentifier] = true;
                }
            }
        }

        // Count the unique items
        $count = count($uniqueItems);

        return $this->respond(['count' => $count]);
    }

    public function branchInventory($branchId)
    {
        $productModel = new ProductModel();

        // Assuming 'products' is your table name and 'branch_id' is the column name
        $products = $productModel->where('branch_id', $branchId)->findAll();

        return $this->respond($products);
    }
}