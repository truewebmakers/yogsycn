<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class yoga_pose extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'name',
        'short_description',
        'long_description',
        'image',
        'pose_type',
        'sanskrit_meaning',
        'benefits',
        'targets',
        'guidance',
        'things_keep_in_mind',
        'category_id',
        'draft','meta_tag'
    ];

    public function poseCategory()
    {
        return $this->belongsTo(pose_category::class, 'category_id');
    }
}

