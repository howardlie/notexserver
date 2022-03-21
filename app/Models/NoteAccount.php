<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteAccount extends Model
{
    use HasFactory;


    protected $fillable = ['note_id', 'account_id'];
    protected $table = 'account_note';
    public $timestamps = false;
}
