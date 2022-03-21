<?php

namespace App\Services;

use DiffMatchPatch\DiffMatchPatch;

class NoteService {

    public static function patchNote($note, $patch) {
        $dmp = new DiffMatchPatch();
        $note->text = $dmp->patch_apply($dmp->patch_fromText($patch['patch']), $note->text)[0];
        $note->version = $patch['version'];
        return $note;
    }




}
