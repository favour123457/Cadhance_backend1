<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscrowHistory extends Model
{
    protected $guarded = [];

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }
}
