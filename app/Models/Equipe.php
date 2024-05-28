<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'etudiant_1_codeApoge',
        'etudiant_2_codeApoge',
        'etudiant_3_codeApoge',
        'encadrant_code',
    ];

    public function etudiant1()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_1_codeApoge', 'codeApoge');
    }

    public function etudiant2()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_2_codeApoge', 'codeApoge');
    }

    public function etudiant3()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_3_codeApoge', 'codeApoge');
    }

    public function encadrant()
    {
        return $this->belongsTo(Encadrant::class, 'encadrant_code', 'encadrant_code');
    }
}