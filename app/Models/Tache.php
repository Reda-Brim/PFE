<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    use HasFactory;

    protected $fillable = [
        'projet_id',
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'etat',
        'feedback',
        'document'
    ];

    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

}