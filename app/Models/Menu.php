<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'language',
        'url',
        'icon',
        'priority',
        'status',
        'parent',
        'type',
        'created_at',
        'updated_at'
    ];
}
