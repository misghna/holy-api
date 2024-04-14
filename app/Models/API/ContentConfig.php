<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentConfig extends Model
{
    use HasFactory;
    protected $table = 'content_config';

    protected $fillable = [
        'content_type',
        'name',
        'description',
        'img_link',
        'parent',
        'header_img',
        'header_text',
        'updated_by'
    ];

}
