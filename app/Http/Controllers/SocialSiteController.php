<?php

namespace App\Http\Controllers;

use App\Models\SocialSite;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSocialSiteRequest;
use App\Http\Requests\UpdateSocialSiteRequest;

class SocialSiteController extends Controller
{
    public function index()
    {
        $sites = SocialSite::get();
        return view('admin.setting.social')->with('sites', $sites);
    }

    public function store(StoreSocialSiteRequest $request)
    {
        $site = SocialSite::create([
            'name' => $request->name,
            'url' => $request->url,
            'code' => $request->code,
        ]);

        return back()->with('success', "Successfully created" . $site->name . " ");


    }

    public function update(UpdateSocialSiteRequest $request, SocialSite $site)
    {
        $site->name = $request->name;
        $site->url = $request->url;
        $site->code = $request->code;
        $site->save();
        return back()->with('success', "Successfully update " . $site->name . " ");

    }
    
    public function destroy(SocialSite $site)
    {
        $site->delete();
        return back()->with('success', "Successfully deleted");
    }
}
