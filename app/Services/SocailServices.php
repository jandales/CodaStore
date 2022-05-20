<?php 
namespace App\Services;

use App\Models\SocialSite;

class SocailServices
{

    public function store(Request $request)
    {
        return  SocialSite::create([
            'name' => $request->name,
            'url' => $request->url,
            'code' => $request->code,
        ]);
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