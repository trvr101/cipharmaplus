<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\NotesModel;

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
        $data = [

            'user_id' => 1, //TODO 'user_id'(the one who add) 
            'note_title' => $this->request->getVar('note_title'),
            'note_content' => $this->request->getVar('note_content'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),

        ];

        $result = $main->save($data);

        if ($result) {
            return $this->respond(['msg' => 'okay']);
        } else {
            return $this->respond(['msg' => 'failed']);
        }
    }
    public function index()
    {
        //
        $main = new NotesModel();
        $data = $main->orderBy('created_at', 'DESC')->findAll();
        return $this->respond($data);
    }
}
