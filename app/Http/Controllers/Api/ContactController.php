<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Mail\SendUsMail;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function sendMessage(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'content' => 'required'
        ]);
        if ($to = GeneralSetting::storeRecipients()) {
            Mail::to($to)->queue(new SendUsMail($request->email, (string) $request->content));
        }
        return response()->json(['status' => 200, 'message' => 'Successfully Sent']);


    }
}
