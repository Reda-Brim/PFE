<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Etudiant extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'nom', 'prenom', 'email', 'codeApoge', 'cin', 'cne', 'filiere', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
    public function equipe()
    {
        return $this->hasOne(Equipe::class, 'etudiant_1_codeApoge', 'codeApoge')
                    ->orWhere('etudiant_2_codeApoge', 'codeApoge')
                    ->orWhere('etudiant_3_codeApoge', 'codeApoge');
    }
}
