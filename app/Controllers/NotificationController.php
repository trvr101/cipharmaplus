<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Models\NotificationReadModel;

class NotificationController extends ResourceController
{
    public function index()
    {
        //
        $main = new NotificationModel();
        $data = $main->findAll();
        return $this->respond($data);
    }
    public function BranchNotifications()
    {
        $token = $this->request->getVar('token');
        $user = new UserModel();
        $notification = new NotificationModel();
        $read = new NotificationReadModel();
        $user_info = $user->where('token', $token)->first();

        // Get all notifications for the branch
        $notifications = $notification->where('branch_id', $user_info['branch_id'])->orderBy('created_at', 'DESC')->findAll();

        // Iterate over notifications to check read status
        foreach ($notifications as &$notification) {
            // Check if the user has read this notification
            $readRecord = $read->where('notification_id', $notification['notification_id'])
                ->where('user_id', $user_info['user_id'])
                ->first();

            // Add read status to the notification
            $notification['read_status'] = $readRecord ? 'read' : 'unread';
        }

        return $this->respond($notifications);
    }
    public function NotificationRead()
    {
        $token = $this->request->getVar('token');
        $user = new UserModel();
        $notification = new NotificationModel();
        $read = new NotificationReadModel();

        // Retrieve the user information based on the token
        $user_info = $user->where('token', $token)->first();
        $user_id = $user_info['user_id'];
        $branch_id = $user_info['branch_id'];

        // Get all notifications for the branch
        $notifications = $notification->where('branch_id', $branch_id)->findAll();

        // Iterate over notifications to check and update read status
        foreach ($notifications as $notification) {
            $notification_id = $notification['notification_id'];

            // Check if the user has read this notification
            $readRecord = $read->where('notification_id', $notification_id)
                ->where('user_id', $user_id)
                ->first();

            // If the notification is not read, mark it as read
            if (!$readRecord) {
                $read->insert([
                    'notification_id' => $notification_id,
                    'user_id' => $user_id,
                    'read_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Return the notifications with the updated read statuses
        return $this->respond($notifications);
    }
}