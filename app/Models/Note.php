<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $guarded = [];

    public function note_type()
    {
        return $this->belongsTo(NoteType::class);
    }
}
