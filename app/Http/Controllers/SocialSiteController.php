<?php

namespace App\Http\Controllers;

use App\Models\SocialSite;
use Illuminate\Http\Request;
use App\Services\SocailServices;
use App\Http\Requests\StoreSocialSiteRequest;
use App\Http\Requests\UpdateSocialSiteRequest;

class SocialSiteController extends Controller
{
    private $services;

    public function __construct(SocailServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        $sites = SocialSite::get();
        return view('admin.setting.social')->with('sites', $sites);
    }

    public function store(StoreSocialSiteRequest $request)
    {
       $site = $this->services->store($request);
       return back()->with('success', "Successfully created" . $site->name . " ");
    }

    public function update(UpdateSocialSiteRequest $request, SocialSite $site)
    {
        $site = $this->services->update($request);
        return back()->with('success', "Successfully update " . $site->name . " ");

    }
    
    public function destroy(SocialSite $site)
    {
        $site->delete();
        return back()->with('success', "Successfully deleted");
    }
}
