<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Internal note on an order; only staff can see it. */
class OrderNote extends Model
{
    protected $fillable = ['order_id', 'admin_id', 'body'];

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function authorName(): string
    {
        if (!$this->admin) {
            return 'Deleted staff member';
        }

        return trim($this->admin->firstname . ' ' . $this->admin->lastname) ?: $this->admin->username;
    }
}
