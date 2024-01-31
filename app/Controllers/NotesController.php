<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\NotesModel;
use App\Models\UserModel;

class NotesController extends ResourceController
{

    public function deleteNote($noteId)
    {
        $main = new NotesModel();
        $note = $main->find($noteId);

        if ($note) {
            $main->delete($noteId);
            return $this->respond(['msg' => 'Note deleted successfully']);
        } else {
            return $this->respond(['msg' => 'Note not found'], 404);
        }
    }
    public function AddNotes()
    {
        $main = new NotesModel();
        $user = new UserModel();
        $token = $this->request->getVar('token');
        $profile = $user->where('token', $token)->first();
        $data = [
            'user_id' => $profile['user_id'],
            'note_title' => $this->request->getVar('note_title'),
            'note_content' => $this->request->getVar('note_content'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),

        ];

        $result = $main->save($data);

        if ($result) {
            return $this->respond(['msg' => 'note added successfully']);
        } else {
            return $this->respond(['msg' => 'failed adding note', 'error' => true]);
        }
    }
    public function notesList($token)
    {
        // Assuming $token is associated with a user
        $main = new UserModel();

        // Find the user by token
        $user = $main->where('token', $token)->first();

        // Check if the user with the given token exists
        if (!$user) {
            return $this->fail('User not found', 404);
        }

        // Fetch all notes for the found user
        $notes = new NotesModel();
        $userNotes = $notes->where('user_id', $user['user_id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->respond($userNotes);
    }
    public function index()
    {
    }
}
