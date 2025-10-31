<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'emplacement',
        'mac_address',
        'actif'
    ];

    // 🔗 Relations
    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }
}
