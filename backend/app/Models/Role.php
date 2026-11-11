<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Menghubungkan model ini ke tabel 'roles' di database safeaset_db
    protected $table = 'roles';

    // Menonaktifkan kolom created_at & updated_at karena tabel roles adalah tabel master statis
    public $timestamps = false;

    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'role_name',
        'description'
    ];

    // Relasi balik ke model User melalui tabel perantara 'user_roles'
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
                    ->withPivot('status');
    }
}