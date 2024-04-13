<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

     /**
     * Get the files associated with the content.
    */
    public function files()
    {
        return $this->hasMany(File::class, 'group_id');
    }
}
