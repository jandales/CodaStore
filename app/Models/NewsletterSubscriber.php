<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'user_id', 'token', 'unsubscribed_at'];

    protected $casts = ['unsubscribed_at' => 'datetime'];

    protected static function booted()
    {
        static::creating(function (self $subscriber) {
            $subscriber->email = mb_strtolower(trim($subscriber->email));
            $subscriber->token = $subscriber->token ?: Str::random(40);
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('unsubscribed_at');
    }

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', [$this->token]);
    }
}
