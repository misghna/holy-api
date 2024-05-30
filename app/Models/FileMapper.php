<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\API\PageConfig; 
use App\Models\API\Content; 
use App\Models\API\File; 

class FileMapper extends Model
{
    use HasFactory;

    protected $table = 'file_mapper';

    protected $fillable = [
        'ref_id',
        'ref_type',
        'file_id',
        'updated_by'
    ];

    public function pageConfig()
    {
        return $this->belongsTo(PageConfig::class, 'ref_id')->where('ref_type', 'page_config');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'ref_id')->where('ref_type', 'content');
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
