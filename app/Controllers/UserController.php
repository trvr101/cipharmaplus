<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\UserModel;
use App\Models\BranchModel;
use App\Models\NotificationModel;


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
            $completeness = 0;
            if (!empty($profile['first_name'])) {
                $completeness++;
            }
            if (!empty($profile['last_name'])) {
                $completeness++;
            }
            if (!empty($profile['email'])) {
                $completeness++;
            }

            $totalFields = 3;
            $percentageOfCompleteness = ($completeness / $totalFields) * 100;
            if ($percentageOfCompleteness == 100) {
                $percentageOfCompleteness = true;
            } else {
                $percentageOfCompleteness = false;
            }
            // Include completeness percentage inside the 'user' array
            $profile['completeness_percentage'] = $percentageOfCompleteness;

            // Return a JSON response with a 200 status code
            return $this->respond([
                'user' => $profile
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
        $user = new UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        // Validate input
        if (empty($email)) {
            return $this->respond(['msg' => 'Email is required', 'error' => true]);
        }

        if (empty($password)) {
            return $this->respond(['msg' => 'Password is required', 'error' => true]);
        }

        // Check if user exists
        $data = $user->where('email', $email)->first();

        if (!$data) {
            return $this->respond(['msg' => 'Invalid email or password', 'error' => true]);
        }

        // Verify password
        $pass = $data['user_password'];
        $authenticatePassword = password_verify($password, $pass);

        if (!$authenticatePassword) {
            return $this->respond(['msg' => 'Invalid email or password', 'error' => true]);
        }

        // Check if the user is active
        if ($data['status'] !== 'active') {
            return $this->respond(['msg' => 'User account is not active', 'error' => true]);
        }

        // Successful login
        return $this->respond([
            'msg' => 'Login successful',
            'token' => $data['token'],
            'user_role' => $data['user_role'],
            'user_id' => $data['user_id'],
        ]);
    }

    public function register()
{
    $user = new UserModel();
    $branch = new BranchModel();
    $notification = new NotificationModel();

    $token = $this->verification(50);
    $invitationCode = $this->request->getVar('invitationCode');

    // Validate inputs
    $email = $this->request->getVar('email');
    $password = $this->request->getVar('password');
    $firstName = $this->request->getVar('first_name');
    $lastName = $this->request->getVar('last_name');
    $phone = $this->request->getVar('phone');

    if (empty($email) || empty($password) || empty($firstName) || empty($lastName) || empty($phone) || empty($invitationCode)) {
        return $this->respond(['msg' => 'All fields are required', 'error' => true]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->respond(['msg' => 'Invalid email format', 'error' => true]);
    }

    // Password validation
    if (!preg_match('/^(?=.*[A-Z])(?=.*\W).{8,}$/', $password)) {
        return $this->respond([
            'msg' => 'Password must be at least 8 characters long, contain an uppercase letter, and a symbol',
            'error' => true
        ]);
    }

    // Check if email already exists
    if ($user->where('email', $email)->first()) {
        return $this->respond(['msg' => 'Email is already registered', 'error' => true]);
    }

    // Check invitation code validity
    $branchData = $branch->where('CS_invite_code', $invitationCode)
        ->orWhere('BA_invite_code', $invitationCode)
        ->first();

    if (!$branchData) {
        return $this->respond(['msg' => 'Invitation code does not exist', 'error' => true]);
    }

    if (!$branchData['is_open_for_invitation']) {
        return $this->respond(['msg' => 'The Branch is not open for Invitation', 'error' => true]);
    }

    // Determine user role and branch ID
    $userRole = ($invitationCode == $branchData['CS_invite_code']) ? 'cashier' : 'branch_admin';

    // Prepare data for insertion
    $data = [
        'email' => $email,
        'user_password' => password_hash($password, PASSWORD_DEFAULT),
        'token' => $token,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone' => $phone,
        'branch_id' => $branchData['branch_id'],
        'status' => 'active',
        'user_role' => $userRole,
    ];

    // Insert new user
    $u = $user->insert($data);

    if ($u) {
        // Add notification
        $user_info = $user->where('token', $token)->first();
        $personnel = ($user_info['user_role'] == 'cashier') ? 'Cashier' : 'Branch Admin';
        $notif = [
            'event_type' => 'user',
            'related_id' => $user_info['user_id'],
            'branch_id' => $user_info['branch_id'],
            'title' => 'New ' . $personnel . ' added',
            'message' => $user_info['first_name'] . ' is registered as ' . $personnel,
        ];
        $notification->insert($notif);

        return $this->respond([
            'msg' => 'Registered successfully on ' . $branchData['branch_name'],
            'token' => $data['token'],
            'user_role' => $userRole,
        ]);
    } else {
        return $this->respond(['msg' => 'Registration unsuccessful', 'error' => true]);
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