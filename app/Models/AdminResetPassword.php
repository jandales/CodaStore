<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminResetPassword extends Model
{
    use HasFactory;

    protected $table = "admin_reset_password";
    public $timestamps = false;
    
     protected $fillable = [       
         'email',
         'token',
         'created_at'      
     ];
}
