<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\UserModel;
use App\Models\BranchModel;


class UserController extends ResourceController
{
    public function BranchUserList($token)
    {
        $userModel = new UserModel();

        // Retrieve the user profile based on the provided token
        $profile = $userModel->where('token', $token)->first();

        if ($profile) {
            // Get the 'branch_id' from the user profile
            $branchId = $profile['branch_id'];

            // Find all users with the same 'branch_id'
            $usersWithSameBranch = $userModel->where('branch_id', $branchId)->findAll();

            // Return the users as JSON
            return $this->respond($usersWithSameBranch);
        } else {
        }
    }
    public function userProfile($token)
    {
    }



    public function profile($token)
    {
        $user = new UserModel();
        $profile = $user->where('token', $token)->first();

        if ($profile) {
            // Return a JSON response with a 200 status code
            return $this->respond([
                'user' => $profile,
            ]);
        } else {
            // Return a JSON response with a 404 status code
            return $this->respond(['msg' => 'User not found'], 404);
        }
    }


    public function userVerify($token)
    {
        $user = new UserModel();
        $password = $this->request->getVar('current_password');

        // Validation
        if (empty($password)) {
            return $this->respond(['msg' => 'error', 'error' => 'Empty password'], 400);
        }

        // Check if the user with the given token exists
        $userV = $user->where('token', $token)->first();

        if ($userV) {
            // Verify the password
            if (password_verify($password, $userV['user_password'])) {
                // Password is correct, you can return user details or any other response
                return $this->respond(['msg' => 'success', 'user' => $userV]);
            } else {
                // Password is incorrect
                return $this->respond(['msg' => 'error', 'error' => 'Invalid password'], 400);
            }
        } else {
            // User with the specified token does not exist
            return $this->respond(['msg' => 'error', 'error' => 'User not found'], 404);
        }
    }


    public function login()
    {
        $user = new UserModel(); //['user_id', 'first_name', 'last_name', 'email', 'user_password', 'phone', 'user_role', 'branch_id', 'status', 'token', 'created_at'];
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
    public function register()
    {
        $user = new UserModel();
        $branch = new BranchModel();
        $token = $this->verification(50);
        $invitationCode = $this->request->getVar('invitationCode');

        // Check if the invitation code exists in the branch
        $branchData = $branch->where('CS_invite_code', $invitationCode)
            ->orWhere('BA_invite_code', $invitationCode)
            ->first();

        if ($branchData) {
            // Invitation code is valid, determine user role and branch ID
            if ($invitationCode == $branchData['CS_invite_code']) {
                // Invitation code belongs to CS_invite_code field
                $userRole = 'cashier';
            } elseif ($invitationCode == $branchData['BA_invite_code']) {
                // Invitation code belongs to BA_invite_code field
                $userRole = 'branch_admin';
            }

            $data = [
                'email' => $this->request->getVar('email'),
                'user_password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'token' => $token,
                'first_name' => 'Robert',
                'last_name' => 'Aguba',
                'branch_id' => $branchData['branch_id'],
                'status' => 'active',
                'user_role' => $userRole,
            ];

            $u = $user->insert($data);
            $user_role = $data['user_role'];
            if ($u) {
                return $this->respond(['msg' => 'okay', 'token' => $data['token'], 'user_role' => $user_role], 201);
            } else {
                return $this->respond(['msg' => 'okay'], 400);
            }
        } else {
            // Invitation code is invalid
            return $this->respond(['msg' => 'invalidInvitationCode'], 400);
        }
    }

    private function verification($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

    public function index()
    {
        $main = new UserModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
}
