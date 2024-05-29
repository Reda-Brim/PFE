<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Projet extends Model
{
    use HasFactory;
    protected $fillable = [
        'sujet_id',
        'equipe_id',
        'date_debut',
        'date_fin',
        'description',
        'etat',
    ];

    public function sujet()
    {
        return $this->belongsTo(Sujet::class);
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }
}
