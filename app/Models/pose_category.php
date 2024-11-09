<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pose_category extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'name',
    ];

    public function yogaPoses()
    {
        return $this->hasMany(yoga_pose::class, 'category_id');
    }
}
