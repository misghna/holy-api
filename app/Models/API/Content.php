<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    protected $table = 'content';
    protected $fillable = [
        'type',
        'title',
        'description',
        'content_text',
        'content_category',
        'lang',
        'is_original',
        'auto_translate',
        'is_draft',
        'background_image',
        'updated_by',
        'tenant_id'
    ];

    public function media_link()
    {
        return $this->hasMany(File::class, 'group_id');
    }
}
