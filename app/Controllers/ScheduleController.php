<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ScheduleModel;
use App\Models\UserModel;

class ScheduleController extends ResourceController
{

    public function SchedList()
    {
        $user = new UserModel();
        $sched = new ScheduleModel();

        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();

        if (!$profile) {
            // Handle case where user with the provided token is not found
            return $this->respond(['error' => 'User not found'], 404);
        }

        $UserSched = $sched->where('branch_id', $profile['branch_id'])->findAll();

        // Filter schedules based on privacy settings
        $filteredSchedules = [];
        foreach ($UserSched as $schedule) {
            if ($schedule['privacy'] == 'only me' && $schedule['user_id'] == $profile['user_id']) {
                $filteredSchedules[] = $schedule;
            } elseif ($schedule['privacy'] == 'branch' && $schedule['branch_id'] == $profile['branch_id']) {
                $filteredSchedules[] = $schedule;
            }
        }

        usort($filteredSchedules, function ($a, $b) {
            return strtotime($a['start_date']) - strtotime($b['start_date']);
        });
        return $this->respond(['schedules' => $filteredSchedules]);
    }


    public function addSched()
    {
        $sched = new ScheduleModel();
        $user = new UserModel();

        // Get request data
        $token = $this->request->getVar('token');
        $eventName = $this->request->getVar('event_name');
        $startDate = $this->request->getVar('start_date');
        $endDate = $this->request->getVar('end_date');
        $description = $this->request->getVar('description'); // Correctly map to 'additional_details'
        $privacy = $this->request->getVar('privacy');

        // Set the timezone to Philippines
        $dateTime = new \DateTime('now', new \DateTimeZone('Asia/Manila'));
        $createdAt = $dateTime->format('Y-m-d H:i:s');

        // Validate token
        if (!$token) {
            return $this->respond(['msg' => 'Token is required', 'error' => true]);
        }

        $profile = $user->where('token', $token)->first();
        if (!$profile) {
            return $this->respond(['msg' => 'Invalid token or user not found', 'error' => true]);
        }

        // Validate event data
        if (empty($eventName)) {
            return $this->respond(['msg' => 'Event name is required', 'error' => true]);
        }

        if (empty($startDate) || empty($endDate)) {
            return $this->respond(['msg' => 'Start date and end date are required', 'error' => true]);
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            return $this->respond(['msg' => 'Start date cannot be later than end date', 'error' => true]);
        }

        if (empty($privacy)) {
            return $this->respond(['msg' => 'Privacy setting is required', 'error' => true]);
        }

        // Prepare data for saving
        $data = [
            'user_id' => $profile['user_id'],
            'branch_id' => $profile['branch_id'],
            'event_name' => $eventName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'additional_details' => $description, // Mapped correctly
            'privacy' => $privacy,
            'created_at' => $createdAt,
        ];

        // Save data to database
        $result = $sched->save($data);

        if ($result) {
            return $this->respond(['msg' => 'Successfully added schedule', 'error' => false]);
        } else {
            return $this->respond(['msg' => 'Adding schedule failed', 'error' => true]);
        }
    }
}
