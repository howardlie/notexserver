<?php

namespace App\Services;

use DiffMatchPatch\DiffMatchPatch;

class NoteService {

    public static function patchNote($note, $patch) {
        $dmp = new DiffMatchPatch();
        $note->text = $dmp->patch_apply($patch['patch'], $note->text);
        $note->version = $patch['version'];
        return $note;
    }




}
