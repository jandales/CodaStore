<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** A content page written in Markdown (Privacy, Terms, FAQ, ...). */
class Page extends Model
{
    public const FOOTER_GROUPS = [
        'help' => 'Footer: Help',
        'company' => 'Footer: Company',
        'none' => 'Not in the footer',
    ];

    protected $fillable = ['slug', 'title', 'meta_description', 'body', 'footer_group', 'published'];

    protected $casts = ['published' => 'boolean'];

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /** Markdown to HTML. Raw HTML in the text is removed, so pasted scripts can't run. */
    public function html(): string
    {
        return (string) Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function url(): string
    {
        return route('page', [$this->slug]);
    }

    /** Published pages for one footer column. */
    public static function footer(string $group)
    {
        // Read once per request; cleared when a page is saved.
        if (!app()->bound('store.footer_pages')) {
            try {
                $pages = static::published()->orderBy('title')->get(['slug', 'title', 'footer_group']);
            } catch (\Throwable $e) {
                $pages = collect(); // table not migrated yet
            }
            app()->instance('store.footer_pages', $pages);
        }

        return app('store.footer_pages')->where('footer_group', $group);
    }

    protected static function booted()
    {
        static::saved(fn () => app()->forgetInstance('store.footer_pages'));
        static::deleted(fn () => app()->forgetInstance('store.footer_pages'));
    }
}
