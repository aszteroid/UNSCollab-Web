<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $table      = 'companies';
    protected $primaryKey = 'id_company';
    protected $keyType    = 'string';
    public    $incrementing = false;
    public    $timestamps = false;

    protected $fillable = [
        'id_company',
        'id_user',
        'company_name',
        'industry_field',
        'contact',
        'description',
        'logo',
        'location',
    ];

    public function internships()
    {
        return $this->hasMany(Internship::class, 'id_company', 'id_company');
    }
}