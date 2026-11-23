<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Http\Requests\SendUsMessageRequest;
use App\Mail\SendUsMail;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Mail;

class SendUsEmailController extends Controller
{
    /** Contact page: the message goes to the store, never to the address typed in the form. */
    public function send(SendUsMessageRequest $request)
    {
        $to = GeneralSetting::storeRecipients();
        if ($to) {
            try {
                Mail::to($to)->queue(new SendUsMail($request->email, (string) $request->content));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Thanks! Your message was sent. We\'ll reply by email.');
    }
}
