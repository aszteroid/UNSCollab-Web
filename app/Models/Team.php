<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    // 1. Nama tabel sesuai skema database Postgres / Supabase kamu
    protected $table = 'teams';

    // 2. Primary key kustom
    protected $primaryKey = 'id_team';

    // 3. Karena tipe datanya UUID (dari gen_random_uuid()), beri tahu Laravel agar tidak membacanya sebagai auto-increment integer
    protected $keyType = 'string';
    public $incrementing = false;

    // 4. Di skema databasemu nama kolomnya 'created_at', jadi kita bisa aktifkan timestamps Laravel 
    // tetapi hanya untuk created_at saja (karena tidak ada kolom updated_at di tabel teams)
    public $timestamps = true;
    const UPDATED_AT = null; 

    // Kolom yang boleh diisi secara massal (Disesuaikan dengan skema database nyata)
    protected $fillable = [
        'id_creator', // FIXED: di skema kamu nama kolom pembuatnya adalah id_creator
        'team_name',
        'category',
        'description',
        'requirement',
        'max_member',
        'deadline',
        'tag',
        'team_logo',
        'created_at' // FIXED: di skema tertulis created_at (pakai 'd')
    ];

    /**
     * Relasi mengambil data Mahasiswa pembuat team (1:N)
     * Di skema kamu: teams.id_creator merujuk ke students.id_student
     */
    public function creator()
    {
        // FIXED: Local key di tabel teams adalah id_creator, mengarah ke id_student di tabel students
        return $this->belongsTo(Student::class, 'id_creator', 'id_student');
    }

    /**
     * Relasi mengambil anggota kelompok (M:N) menggunakan tabel jembatan team_members
     * Relasi ini menghubungkan model Team langsung ke model Student melalui tabel pivot
     */
    public function members()
    {
        // FIXED: Menggunakan belongsToMany karena ini relasi Many-to-Many melalui tabel jembatan 'team_members'
        return $this->belongsToMany(Student::class, 'team_members', 'id_team', 'id_student')
                    ->withPivot('join_status', 'join_at'); // Mengikutkan kolom ekstra di tabel jembatan jika nanti dibutuhkan
    }
}