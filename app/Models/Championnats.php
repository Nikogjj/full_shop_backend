<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Championnats extends Model
{
    protected $fillable = [
        'name',
        'pays'
    ];
    protected $table = 'championnats';
}
