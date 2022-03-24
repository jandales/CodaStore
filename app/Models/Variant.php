<?php

namespace App\Models;

use App\Models\Varaint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory;

      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_id',
        'value'     
    ];

    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'variant_id', 'id');
     
    }

    public function scopeExist($query,$name)
    {   
        return $query->where('value', $name);               
                
    }
}
