<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\UserModel;

class UserController extends ResourceController
{
    public function register()
    {
        $user = new UserModel();
        $token = $this->verification(50);
        $data = [
            'email' => $this->request->getVar('email'),
            'user_password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'token' => $token,
            'first_name' => 'Robert',
            'last_name' => 'Aguba',
            'branch_id' => 1,
            'status' => 'active',
            'user_role' => 'admin',
        ];

        $u = $user->insert($data);

        if ($u) {
            return $this->respond(['msg' => 'okay', 'token' => $token], 201);
        } else {
            return $this->respond(['msg' => 'failed'], 400);
        }
    }
    private function verification($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

    public function login()
    {
        $user = new UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        if (empty($password)) {
            return $this->respond(['msg' => 'error', 'error' => 'Empty password'], 400);
        }

        $data = $user->where('email', $email)->first();

        if ($data) {
            $pass = $data['user_password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                // Fetch user role from the database
                $user_role = $data['user_role'];

                // Include user role in the response
                return $this->respond(['msg' => 'okay', 'token' => $data['token'], 'user_role' => $user_role], 201);
            } else {
                return $this->respond(['msg' => 'error'], 401);
            }
        } else {
            return $this->respond(['msg' => 'error'], 401);
        }
    }
    public function index()
    {
        $main = new UserModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}