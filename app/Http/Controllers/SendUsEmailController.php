<?php

namespace App\Http\Controllers;

use App\Mail\SendUsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SendUsMessageRequest;

class SendUsEmailController extends Controller
{
    public function send(SendUsMessageRequest $request)
    {   
        $to_email = siteSettings()->site_email;
        $from_email = $request->email;        
        Mail::to($to_email)->send(new SendUsMail($from_email, $request->content));
        return back()->with('success', 'Successfully Sent');
    }
}
