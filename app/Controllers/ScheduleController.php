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
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $data = [
            'user_id' => $profile['user_id'],
            'branch_id' => $profile['branch_id'],
            'event_name' => $this->request->getVar('event_name'),
            'start_date' => $this->request->getVar('start_date'),
            'end_date' => $this->request->getVar('end_date'),
            'additional_details' => $this->request->getVar('additional_details'),
            'privacy' => $this->request->getVar('privacy'),
        ];
        $result = $sched->save($data);
        if ($result) {
            return $this->respond(['msg' => 'Successfully added schedule']);
        } else {
            return $this->respond(['msg' => 'Adding schedule failed', 'error' => true]);
        }
    }
}
