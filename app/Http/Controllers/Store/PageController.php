<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        // Drafts are visible only to signed-in staff, as a preview.
        $preview = !$page->published;
        abort_if($preview && !auth('admin')->check(), 404);

        return view('page', ['page' => $page, 'preview' => $preview]);
    }
}
