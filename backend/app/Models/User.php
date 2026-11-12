<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'email_hash', // Tambahan untuk pencarian login email
        'phone',
        'password_hash',
        'status',
        'failed_login_attempts',
        'lockout_until',
    ];

    protected $hidden = [
        'password_hash',
        'email_hash',
    ];

    // Otomatis mengenkripsi email & phone di database, dan mendekripsinya saat dipanggil di web
    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'phone' => 'encrypted',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withPivot('status');
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }
}