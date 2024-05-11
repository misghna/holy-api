<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    protected $table = 'dictionary';
    use HasFactory;
    protected $fillable = [
        'key',
        'language',
        'tenant_id',
        'value',
        'updated_by'
    ];  
}
