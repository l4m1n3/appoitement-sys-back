<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'badge_id',
        'date_heure',
        'type',
        'latitude',
        'longitude',
        'source',
    ];

    // 🔗 Relations
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    public function portique()
    {
        return $this->belongsTo(Portique::class);
    }
}
