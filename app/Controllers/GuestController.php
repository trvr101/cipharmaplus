<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProductModel;
use App\Models\ProductCategoryModel;
use App\Models\UserModel;
use App\Models\SearchMedModel;
use App\Models\GuestUserModel;
use App\Models\GuestUserLogModel;

class GuestController extends ResourceController
{
    use ResponseTrait;

    public function logVisit()
    {
        // Get session_id from cookie or create new session_id if it doesn't exist
        $session_id = $this->request->getPost('session_id') ?? $this->generateSessionId();

        // Check if this session_id exists in guest_user_tbl
        $guestUserModel = new GuestUserModel();
        $guestUser = $guestUserModel->where('session_id', $session_id)->first();

        // Check if the user is new or returning
        if ($guestUser) {
            $visit_type = 'returnee';
            $guest_user_id = $guestUser['guest_user_id']; // Existing user
        } else {
            // New user, create a new guest record
            $visit_type = 'new';
            $guest_user_id = $this->createNewGuestUser($session_id);
        }

        // Log the visit
        $this->logUserVisit($guest_user_id, $session_id, $visit_type);

        return $this->respondCreated(['message' => 'Visit logged successfully!']);
    }

    private function generateSessionId()
    {
        // Generate a new session_id (e.g., UUID or random string)
        return bin2hex(random_bytes(16));
    }

    private function createNewGuestUser($session_id)
    {
        // Create a new guest user and insert into guest_user_tbl
        $guestUserModel = new GuestUserModel();
        $data = [
            'session_id' => $session_id,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $guestUserModel->insert($data);
    }

    private function logUserVisit($guest_user_id, $session_id, $visit_type)
    {
        // Log the visit in guest_user_log_tbl
        $guestUserLogModel = new GuestUserLogModel();
        $data = [
            'guest_user_id' => $guest_user_id,
            'visit_type' => $visit_type,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $guestUserLogModel->insert($data);
    }
}
