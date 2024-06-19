<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\API\PageConfig;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

     public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function readablePages()
    {
        return $this->belongsToMany(PageConfig::class, 'permissions')
                    ->withPivot('access_level')
                    ->wherePivotIn('access_level', ['READ', 'WRITE', 'ADMIN']);
    }

    public function writablePages()
    {
        return $this->belongsToMany(PageConfig::class, 'permissions')
                    ->withPivot('access_level')
                    ->wherePivotIn('access_level', ['WRITE', 'ADMIN']);
    }

    public function adminPages()
    {
        return $this->belongsToMany(PageConfig::class, 'permissions')
                    ->withPivot('access_level')
                    ->wherePivot('access_level', 'ADMIN');
    }

    public function canUpdatePermissions()
    {
        return $this->adminPages()->exists();
    }
}
