<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    use HasFactory;
    /**
* The attributes that are mass assignable.
*
* @var array
*/
protected $fillable = [
    'product_id',
    'path',
    'owner',
    'name',
    'location'  
];



}
