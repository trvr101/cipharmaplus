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

        // Get request data
        $token = $this->request->getVar('token');
        $noteTitle = $this->request->getVar('note_title');
        $noteContent = $this->request->getVar('note_content');

        // Check if the token is provided
        if (!$token) {
            return $this->respond(['msg' => 'Token is required', 'error' => true]);
        }

        // Verify user by token
        $profile = $user->where('token', $token)->first();
        if (!$profile) {
            return $this->respond(['msg' => 'Invalid token or user not found', 'error' => true]);
        }

        // Validate note title and content
        if (empty($noteTitle)) {
            return $this->respond(['msg' => 'Note title is required', 'error' => true]);
        }

        if (empty($noteContent)) {
            return $this->respond(['msg' => 'Note content is required', 'error' => true]);
        }

        // Prepare data for insertion
        $data = [
            'user_id' => $profile['user_id'],
            'note_title' => $noteTitle,
            'note_content' => $noteContent,
            'status' => 'pending',
        ];

        // Save data to database

        $result = $main->save($data);

        if ($result) {
            return $this->respond(['msg' => 'Note added successfully', 'error' => false]);
        } else {
            return $this->respond(['msg' => 'Failed to add note', 'error' => true]);
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
    public function index() {}
}
