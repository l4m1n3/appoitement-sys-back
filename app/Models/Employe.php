<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'date_naissance',
        'service_id',
        'poste_id'
    ];

    // 🔗 Relations
    public function badges()
    {
        return $this->hasMany(Badge::class);
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }
}
