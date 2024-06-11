<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\API\PageConfig;


class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_config_id',
        'access_level'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pageConfig()
    {
        return $this->belongsTo(PageConfig::class);
    }
}
