<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Encadrant extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'specialite', 'password','encadrant_code',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function equipes()
    {
        return $this->hasMany(Equipe::class, 'encadrant_code', 'encadrant_code');
    }
}

