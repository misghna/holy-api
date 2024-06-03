<?php


namespace App\Models\API;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FileMapper;
use App\Models\User; 

class PageConfig extends Model
{
    use HasFactory;
    protected $table = 'page_config';

    protected $fillable = [
        'page_type',
        'page_url',
        'name',
        'description',
        'parent',
        'header_text',
        'updated_by',
        'tenant_id',
        'language',
        'seq_no'
    ]; 

    public function headerImages()
    {
        return $this->hasMany(FileMapper::class, 'ref_id')->where('ref_type', 'page_config')->where('type', 'header_image');
    }

    public function usersWithAccess()
    {
        return $this->belongsToMany(User::class, 'permissions')
                    ->withPivot('access_level');
    }

}
