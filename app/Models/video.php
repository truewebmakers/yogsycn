<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class video extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'title',
        'description',
        'url',
        'related_poses'
    ];
}
