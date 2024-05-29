<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $fillable = [
        'tache_id',
        'lien',
    ];

    public function tache()
    {
        return $this->belongsTo(Tache::class);
    }
}
