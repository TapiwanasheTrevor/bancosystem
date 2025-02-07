<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['name', 'type', 'schema'];
    protected $casts = ['schema' => 'array'];

}
