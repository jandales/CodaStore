<?php

namespace App\Http\Controllers;

use App\Mail\SendUsMail;
use Illuminate\Http\Request;
use App\Jobs\ProccessSendMessage;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SendUsMessageRequest;

class SendUsEmailController extends Controller
{
    public function send(SendUsMessageRequest $request)
    {   
        $from_email = $request->email;  
        dispatch(new ProccessSendMessage($from_email,$request->content));      
        // Mail::to($to_email)->send(new SendUsMail($from_email, $request->content));
        return back()->with('success', 'Successfully Sent');
    }
}

