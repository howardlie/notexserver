<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $patch
 * @property integer $version
 * @property string $note_id
 * @property string $datetime
 */
class NotesPatch extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;


    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = ['patch', 'version', 'note_id', 'datetime', 'editor_name', 'editor_email', 'editor_device'];
}
