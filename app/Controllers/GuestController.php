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
        // Get session_id from the request
        $session_id = $this->request->getVar('session_id');

        // Check if this session_id exists in guest_user_tbl
        $guestUserModel = new GuestUserModel();
        $guestUser = $guestUserModel->where('session_id', $session_id)->first();

        // Determine user type and fetch or create guest_user_id
        if ($guestUser) {
            $visit_type = 'returnee';
            $guest_user_id = $guestUser['guest_user_id'];
        } else {
            $visit_type = 'new';
            $guest_user_id = $this->createNewGuestUser($session_id);
        }

        // Log the visit
        $this->logUserVisit($guest_user_id, $session_id, $visit_type);

        // Respond with the guest_user_id for frontend tracking
        return $this->respondCreated([
            'message' => 'Visit logged successfully!',
            'guest_user_id' => $guest_user_id
        ]);
    }

    // Helper function to log the user's visit
    private function logUserVisit($guest_user_id, $session_id, $visit_type)
    {
        $guestUserLogModel = new GuestUserLogModel();
        $guestUserLogModel->insert([
            'guest_user_id' => $guest_user_id,
            'visit_type' => $visit_type,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Helper function to create a new guest user
    private function createNewGuestUser($session_id)
    {
        $guestUserModel = new GuestUserModel();
        $newGuest = [
            'session_id' => $session_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        return $guestUserModel->insert($newGuest);
    }






    public function searchMed()
    {
        // ['search_id', 'guest_user_id', 'searched_med', 'created_at'];
        $guestuser = new GuestUserModel();
        $searchedMed = $this->request->getVar('searched_med');
        $session_id = $this->request->getVar('session_id');
        $guest_info = $guestuser->where('session_id', $session_id)->first();

        // Load SearchMedModel to query the searched product
        $searchMedModel = new SearchMedModel();

        // Perform the search query (example query using LIKE)

        // Optionally log the search in guest user logs if required
        $searchMed = new SearchMedModel();
        $inserting = $searchMed->insert([
            'guest_user_id' => $guest_info['guest_user_id'],
            'searched_med' => $searchedMed,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Return the search results
        if ($inserting) {
            return $this->response->setJSON([
                'status' => 'success',
            ]);
        }
    }






    public function countTodayNewVisits()
    {
        $guestUserLogModel = new GuestUserLogModel();
        $today = date('Y-m-d');

        // Count new visits for today
        $newVisitCount = $guestUserLogModel
            ->where('visit_type', 'new')
            ->like('created_at', $today, 'after')
            ->countAllResults();

        return $this->respond(['new_visits_today' => $newVisitCount]);
    }
    public function countOverallNewVisits()
    {
        $guestUserLogModel = new GuestUserLogModel();

        // Count overall new visits
        $newVisitCount = $guestUserLogModel
            ->where('visit_type', 'new')
            ->countAllResults();

        return $this->respond(['overall_new_visits' => $newVisitCount]);
    }
    public function countTodayReturnees()
    {
        $guestUserLogModel = new GuestUserLogModel();
        $today = date('Y-m-d');

        // Count returnee visits for today
        $returneeCount = $guestUserLogModel
            ->where('visit_type', 'returnee')
            ->like('created_at', $today, 'after')
            ->countAllResults();

        return $this->respond(['returnees_today' => $returneeCount]);
    }
    public function countOverallReturnees()
    {
        $guestUserLogModel = new GuestUserLogModel();

        // Count overall returnee visits
        $returneeCount = $guestUserLogModel
            ->where('visit_type', 'returnee')
            ->countAllResults();

        return $this->respond(['overall_returnees' => $returneeCount]);
    }
}
