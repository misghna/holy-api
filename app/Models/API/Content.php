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
        'background_image',
        'content_text',
        'media_link',
        'content_category',
        'lang',
        'is_original',
        'auto_translate',
        'is_draft',
        'updated_by',
        'tenant_id'
    ];

    public function media_link()
    {
        return $this->hasMany(File::class, 'group_id');
    }
}
