<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sujets extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nom',
        'document',
        'document_hash',
        'disponible',
    ];
}
