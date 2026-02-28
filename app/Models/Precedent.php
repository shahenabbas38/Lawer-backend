<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Precedent extends Model
{
    // #comment الحقول المسموح تعبئتها عند الإنشاء/التحديث
    protected $fillable = [
        'title',
        'file_path',
        'file_type',
        'allow_download',
        'is_active',
    ];
}

