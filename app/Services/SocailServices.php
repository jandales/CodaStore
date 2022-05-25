<?php 
namespace App\Services;

use App\Models\SocialSite;
use Illuminate\Http\Request;

class SocailServices
{

    public function store(Request $request)
    {
        $validated = $request->validated();
        return  SocialSite::create($validated);
    }

    public function update(Request $request, SocialSite $site)
    {
        $site->name = $request->name;
        $site->url = $request->url;
        $site->code = $request->code;
        $site->save();
        return $site;
    }


}