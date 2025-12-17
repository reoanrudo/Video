<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'analysis_schema_version',
        'analysis_json',
        'analysis_kva_raw',
    ];

    protected $casts = [
        'analysis_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
