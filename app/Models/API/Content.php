<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    protected $table = 'content';

    /**
     * Get the files associated with the content.
    */
    public function files()
    {
        return $this->hasMany(File::class, 'group_id');
    }
}
