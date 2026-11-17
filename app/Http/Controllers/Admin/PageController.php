<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('title')->get()]);
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page(['footer_group' => 'help'])]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validated($request));

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request, $page));

        return back()->with('success', $page->published ? 'Page saved and published.' : 'Draft saved. Customers can\'t see it until you publish it.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages')->with('success', 'Page deleted.');
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'max:80', Rule::unique('pages', 'slug')->ignore(optional($page)->id)],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:100000'],
            'footer_group' => ['required', Rule::in(array_keys(Page::FOOTER_GROUPS))],
        ], [
            'slug.unique' => 'Another page already uses this web address.',
        ]);
        $data['published'] = $request->boolean('published');

        return $data;
    }
}
