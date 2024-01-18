<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\UserModel;
use App\Models\BranchModel;


class UserController extends ResourceController
{
    public function UpdatedPassword()
    {
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $newPassword = $this->request->getVar('new_password');

        $profile = $user->where('token', $token)->first();

        if ($profile) {
            $currentPassword = $this->request->getVar('password');

            // Verify the current password
            if (password_verify($currentPassword, $profile['user_password'])) {
                // Generate a hash for the new password
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update the user's password
                $changed = $user->update($profile['user_id'], ['user_password' => $hashedNewPassword]);
                if ($changed) {
                    return $this->respond(['msg' => 'Password changed successfully']);
                } else {
                    return $this->respond(['msg' => 'Password changed unsuccessfully', 'error' => true]);
                }
            } else {
                return $this->respond(['msg' => 'Current password is incorrect', 'error' => true]);
            }
        } else {
            return $this->respond(['msg' => 'User not found', 'error' => true]);
        }
    }



    public function UpdatedProfile()
    {
        $user = new UserModel();
        $token = $this->request->getVar('token');

        // Retrieve user based on the token
        $profile = $user->where('token', $token)->first();

        if ($profile) {
            // Assuming you want to update these fields from request data
            $data = [];

            // Check if each field is not null before adding it to the $data array
            $first_name = $this->request->getVar('first_name');
            if ($first_name != null) {
                $data['first_name'] = $first_name;
            }

            $last_name = $this->request->getVar('last_name');
            if ($last_name != null) {
                $data['last_name'] = $last_name;
            }

            $email = $this->request->getVar('email');
            if ($email != null) {
                $data['email'] = $email;
            }

            $phone = $this->request->getVar('phone');
            if ($phone != null) {
                $data['phone'] = $phone;
            }

            // Update the user profile
            $updating = $user->update($profile['user_id'], $data);

            if ($updating) {
                return $this->respond(['msg' => 'Updated Successfully']);
            } else {
                return $this->respond(['msg' => 'Update Unsuccessfully', 'error' => true]);
            }
        }
    }



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
            return $this->respond(['msg' => 'User not found', 'error' => true], 404);
        }
    }


    public function userVerify($token)
    {
        $user = new UserModel();
        $password = $this->request->getVar('current_password');

        // Validation
        if (empty($password)) {
            return $this->respond(['msg' => 'error', 'error' => 'Empty password', 'error' => true], 400);
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
            return $this->respond(['msg' => 'Empty password', 'error' => true], 400);
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
            return $this->respond(['msg' => 'error', 'error' => true], 401);
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
            return $this->respond(['msg' => 'invalidInvitationCode', 'error' => true], 400);
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
