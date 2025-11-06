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
        'poste_id',
        'user_id'
    ];

    // 🔗 Relations

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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge() 
    {
        return $this->hasOne(Badge::class);
    }

}
