<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table      = 'users';
    protected $primaryKey = 'id_user';

    // FIX: Primary key UUID, bukan auto-increment integer
    protected $keyType    = 'string';
    public    $incrementing = false;

    // FIX: timestamps = false karena tabel hanya punya created_at, tidak ada updated_at
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'email',
        'password',
        'role',       // enum: 'company' | 'admin'
        'created_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'id_user', 'id_user');
    }
}