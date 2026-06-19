<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'students';

    // Primary key kustom
    protected $primaryKey = 'id_student';

    // WAJIB TAMBAHKAN INI: Beritahu Laravel bahwa primary key-nya bertipe string (UUID)
    protected $keyType = 'string';

    // Beritahu Laravel bahwa kuncinya bukan auto-increment integer
    public $incrementing = false;

    // Matikan timestamps default jika tidak digunakan, atau sesuaikan
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'full_name',
        'nim',
        'major',
        'bio',
        'profile_picture',
        'portofolio',
        'experience',
        'skill'
    ];
}