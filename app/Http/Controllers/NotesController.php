<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\NotesPatch;

class NotesController extends Controller
{

    public function sync() {

    }

    public function getShared($id) {
        $note = Note::where('shared', 1)->where('id', $id)->first();
        if (empty($note)) {
            return response()->json(['status' => 'Error', 'message' => 'Note not Found']);
        }

        
        // check if note_account has exists
        // create new note_account data and send note back
    }
}
