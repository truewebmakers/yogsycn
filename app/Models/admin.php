<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class admin extends Model
{
    use HasFactory, HasApiTokens;
    protected $primaryKey = '_id';
    // public $incrementing = false; // Indicate that the primary key is not an incrementing integer
    // protected $keyType = 'string'; // Specify the type of the primary key
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'username',
        'password'
    ];
    // public function getAuthIdentifierName()
    // {
    //     return '_id'; // Change to your identifier column name if different
    // }
}
