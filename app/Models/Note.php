<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $title
 * @property string $text
 * @property string $account_id
 * @property string $reminder_datetime
 * @property boolean $shared
 */
class Note extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';


    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['title', 'text', 'account_id', 'reminder_datetime', 'shared', 'created_at', 'updated_at', 'id', 'text', 'version'];

    public function shared_to()
    {
        return $this->belongsToMany(Note::class);
    }

    public function patches()
    {
        return $this->hasMany(NotesPatch::class);
    }
}
