<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPortfolioMedia extends Model
{
    protected $guarded = [];

    public function document_type()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
