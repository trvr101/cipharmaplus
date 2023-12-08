<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\ScheduleModel;

class ScheduleController extends ResourceController
{
    public function SchedList()
    {
        //
        $main = new ScheduleModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function AddSched()
    {
        $main = new ScheduleModel();

        $data = [
            'user_id' => 1, // TODO: Replace with the actual user ID
            'event_name' => $this->request->getVar('event_name'),
            'start_date' => $this->request->getVar('startdate'),
            'end_date' => $this->request->getVar('enddate'),
            'additional_details' => $this->request->getVar('description'),
            'privacy' => $this->request->getVar('privacy'),
        ];

        try {
            $result = $main->save($data);

            if ($result) {
                return $this->respond(['msg' => 'success']);
            } else {
                return $this->respond(['msg' => 'failed to save']);
            }
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            log_message('error', 'AddSched error: ' . $e->getMessage());

            return $this->respond(['msg' => 'internal server error'], 500);
        }
    }
}