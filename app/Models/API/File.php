<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group_id','file_id','file_type','file_name','tenant_id','created_at','created_by'
    ];

    /**
     * Get the content document that owns the file.
    */
    public function content()
    {
        return $this->belongsTo(Document::class, 'group_id');
    }
}
