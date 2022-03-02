<?php

namespace App\Http\Controllers;

use Jfcherng\Diff\DiffHelper;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function index() {
        $old = 'This is the old string.';
        $new = 'And this is the new one.';
        $rendererName = "JsonText";
        $result = DiffHelper::calculate($old, $new, $rendererName);
        var_dump($result);
    }

    public function getShared($id) {

    }
}
