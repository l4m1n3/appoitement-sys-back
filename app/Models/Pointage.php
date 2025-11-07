<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory;

    // app/Models/Pointage.php
protected $fillable = [
    'employe_id',
    'badge_id',
    'portique_id',
    'type',
    'methode',
    'ip_address',
    'user_agent',
];

public function portique()
{
    return $this->belongsTo(Portique::class);
}

public function employe()
{
    return $this->belongsTo(Employe::class);
}

public function badge()
{
    return $this->belongsTo(Badge::class);
}
}
