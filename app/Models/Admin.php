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
        'role',
        'permissions',
    ];

    public const ROLE_ADMINISTRATOR = 1;
    public const ROLE_STAFF = 2;

    /**
     * What staff can be given access to: key => [label, what it covers].
     * Administrators have all of these plus Staff users and the activity log.
     */
    public const PERMISSIONS = [
        'products' => ['Products', 'Products, categories, attributes, inventory and CSV import'],
        'orders' => ['Orders', 'Orders, payments, shipping updates, invoices, packing slips and notes'],
        'refunds' => ['Refunds', 'Cancelling paid orders and approving or declining returns (needs Orders)'],
        'customers' => ['Customers', 'Customers, reviews and contact messages'],
        'coupons' => ['Coupons', 'Creating and editing coupons'],
        'reports' => ['Reports', 'Sales reports: revenue, best sellers, customers and CSV downloads'],
        'settings' => ['Settings', 'Store settings, company address, shipping methods and social links'],
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
        'permissions' => 'array',

    ];

    public function fullName()
    {

        $fullname =  $this->firstname . " " . $this->lastname;

        return empty($fullname) ? '---' : $fullname;

    }

    public function userRole()
    {
        return $this->role == 1 ? 'Administrator' : 'Staff';
    }

    /** Administrators can do everything; staff only what is ticked for them. */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_admin()) {
            return true;
        }

        return $permission !== 'staff' && in_array($permission, (array) $this->permissions, true);
    }

    /** Labels of the areas this account can use, for lists. */
    public function permissionLabels(): array
    {
        if ($this->is_admin()) {
            return ['Everything'];
        }

        return collect((array) $this->permissions)
            ->map(fn ($key) => self::PERMISSIONS[$key][0] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    public function is_admin()
    {
        return $this->role == 1 ? true : false;
    }

    public function avatar()
    {
        return $this->imagePath ?? '/img/avatar/admin/default-avatar.png';
    }

    public function scopeSearch($query, $input)
    {
        return $query->where('username', 'like', '%' . $input . '%')
                     ->orWhere('email','like','%' . $input . '%')
                     ->orWhere('firstname','like','%' . $input . '%')
                     ->orWhere('lastname','like','%' . $input . '%');

    }
}
