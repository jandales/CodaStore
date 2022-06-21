<?php

namespace App\Models;

use App\Http\Traits\Crypted;
use App\Http\Traits\DateTimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, Crypted, DateTimeFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',        
        'firstname',
        'lastname',
        'password',
        'role'        
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function fullName()
    {
        $fullname =  $this->firstname . " " . $this->lastname;        
        return empty($fullname) ? '---' : $fullname;
    }
    public function userRole()
    {
        return $this->role == 1 ? 'Administrator' : 'Employee';
    }

    public function is_admin()
    {
        return $this->role == 1 ? true : false;
    }

    public function avatar()
    {
        return $this->imagePath ?? '/img/avatar/admin/default-avatar.jpg';
    }
    public function scopeSearch($query, $input)
    {
    
        return $query->where('username', 'like', '%' . $input . '%')
                     ->orWhere('email','like','%' . $input . '%')
                     ->orWhere('firstname','like','%' . $input . '%')
                     ->orWhere('lastname','like','%' . $input . '%');
       
    }
}
