<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Jobs\ProccessSendMessage;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function sendMessage(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'content' => 'required'
        ]);

        $from_email = $request->email;  
        dispatch(new ProccessSendMessage($from_email,$request->content));      
        // Mail::to($to_email)->send(new SendUsMail($from_email, $request->content));
        return response()->json(['status' => 200, 'message' => 'Successfully Sent']);


    }
}
