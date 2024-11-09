<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class article extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'title',
        'short_description',
        'long_description',
        'image',
        'author_name',
        'author_image',
        'author_details',
        'is_latest',
        'is_expert_approved',
        'draft',
        'category_id',
        'related_poses','meta_tag'
    ];

    public function articleCategory()
    {
        return $this->belongsTo(article_category::class, 'category_id');
    }
}
