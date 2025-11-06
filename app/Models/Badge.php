<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'code_unique',
        'type',
        'actif'
    ];

    // 🔗 Relations 
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
    

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }
}
