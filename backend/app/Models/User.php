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
        'phone',
        'password_hash',
        'status',
        'failed_login_attempts',
        'lockout_until',
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Relasi ke tabel roles melalui tabel pivot user_roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withPivot('status');
    }

    // Fungsi bantuan untuk mengecek role user
    public function hasRole($roleName)
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }
}
