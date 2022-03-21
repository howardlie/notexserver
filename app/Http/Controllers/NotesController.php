<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\NotesPatch;
use App\Models\NoteAccount;
use App\Services\NoteService;
use Ramsey\Uuid\Uuid;

class NotesController extends Controller
{

    public function sync() {
        $client_notes = request()->input('notes');
        $account = auth()->user()->account;
        $NoteDB = new Note();
        $response_notes = [];
        $response_patchs = [];
        $duplicate_notes = [];
        //array_push($response_notes, $account);
        $shared_server_notes = $account->shared_notes->toArray();
        $server_notes = $NoteDB->where('account_id', $account->id)->get()->toArray();
        $server_notes = array_merge($server_notes, $shared_server_notes);
        //$server_shared_notes; //get server shared notes for this account

        foreach ($client_notes as $key => $client_note) {
            $server_notes_key = array_search($client_note['id'], array_column($server_notes, 'id'));
            if ($server_notes_key === false) {
                $note = new Note([
                    'title' => $client_note['title'],
                    'created_at' => $client_note['created_at'],
                    'updated_at' => $client_note['updated_at'],
                    'account_id' => $client_note['account_id'],
                    'shared' => $client_note['shared'],
                    'version' => 0,
                    'status' => $client_note['status'],
                    'reminder_datetime' => $client_note['reminder_datetime'],
                    'id' => $client_note['id'],
                    'text' => ''
                ]);
                $note->save();
            } else {
                $server_notes[$server_notes_key]['exist'] = true;
                $note = $server_notes[$server_notes_key];
                $note = new Note($note);
            }
            // tidak mungkin versi server lebih low dr ekspektasi
            // if patch if empty (cuman update)
            if (empty($client_note['patches'])) {
                if ($note->version == $client_note['version'] && (md5($note->text) == $client_note['hash'])) {
                    //update status
                    $note->status = $client_note['status'];
                    $note->reminder_datetime = $client_note['reminder_datetime'];
                    $note->shared = $client_note['shared'];


                    // if server lebih tinggi maka update, client tidak mungkin lebih tinggi kalau tanpa patch
                } elseif (($note->version - $client_note['version']) <= 3) {
                    $patch = NotesPatch::where('note_id', $note->id)->whereIn('version', [$note->version, $note->version-1, $note->version-2])->get();
                    //send patch
                    array_push($response_patchs, $patch);
                } else {
                    //send full text
                    array_push($response_notes, $note);
                }

            } else {
                // ngasih update
                if (($client_note['patches'][0]['version']-1) == $note->version) {
                    // client version is higher
                    // berarti selaraskan

                    foreach ($client_note['patches'] as $patch_key => $patch_value) {

                        $note = NoteService::patchNote($note, $patch_value);
                        $note_patch = new NotesPatch($patch_value);
                        $note_patch->id = Uuid::uuid6();
                        $note_patch->save();

                    }
                    $note->status = $client_note['status'];
                    $note->created_at = $client_note['created_at'];
                    $note->updated_at = $client_note['updated_at'];
                    $note->title = $client_note['title'];
                    $note->reminder_datetime = $client_note['reminder_datetime'];
                    $note->shared = $client_note['shared'];

                } else {
                    array_push($response_notes, $note);
                    array_push($duplicate_notes, $note->id);
                    // suruh client duplicate
                }
            }
            Note::where('id', $note->id)->update($note->toArray());





        }

        foreach ($server_notes as $key => $server_note) {
            if (!array_key_exists('exist', $server_note)) {
                if ($server_note['status'] != -1) {
                    array_push($response_notes, $server_note);
                }

            }
        }

        //remove patch DB older than 3 version
        return response()->json(['status' => 'OK', 'notes' => $response_notes, 'note_patchs' => $response_patchs, 'duplicate_notes' => $duplicate_notes]);
    }

    public function getShared($id) {
        $note_account = new NoteAccount();
        $note = Note::where('shared', 1)->where('id', $id)->where('status', 1)->first();
        if (empty($note)) {
            return response()->json(['status' => 'Error', 'message' => 'Note not Found']);
        } else {
            $note_account = $note_account->firstOrCreate(['note_id' =>  $id, 'account_id' => auth()->user()->account_id],
            ['note_id' =>  $id, 'account_id' => auth()->user()->account_id]);
            return response()->json(['status' => 'OK', 'note' => $note]);
        }


    }
}
