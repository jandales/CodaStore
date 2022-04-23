<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'site_name',
        'tag_line',
        'site_url',
        'site_email',
        'timezone',
        'date_format',
        'time_format',        
    ];

   
}
