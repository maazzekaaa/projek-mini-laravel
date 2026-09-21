<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'week',
        'matkul',
        'tugas',
        'deadline',
        'tipe',
        'status',
        'files_media',
        'catatan',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];
}