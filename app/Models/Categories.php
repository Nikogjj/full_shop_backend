<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    protected $fillable = [
        'name',
        'parent_id'
    ];
    protected $table = 'categories';

    // use SoftDeletes;
}
