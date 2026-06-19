<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Internship extends Model
{
    use HasFactory;

    // 1. Nama tabel di database kamu
    protected $table = 'internships';

    // 2. Beritahu Laravel bahwa primary key-nya adalah id_internship, bukan id
    protected $primaryKey = 'id_internship';

    // 3. Matikan timestamps default (created_at & updated_at) jika di database tidak menggunakannya
    public $timestamps = false;

    // Kolom-kolom yang diperbolehkan untuk diisi secara massal
    protected $fillable = [
        'id_company',
        'position',
        'description',
        'requirement',
        'salary',
        'location',
        'type', // Lowongan magang WFH / WFO / Hybrid
        'deadline',
        'create_at'
    ];

    /**
     * Relasi mengambil data Perusahaan yang membuka lowongan magang ini (1:N)
     * Menghubungkan id_company di tabel internship ke id_company di tabel company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }
}