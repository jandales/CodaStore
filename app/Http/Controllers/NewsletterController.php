<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $thanks = "Thanks, you're on the list.";

        // Bots fill the hidden "website" field; act as if it worked.
        if (filled($request->input('website'))) {
            return $this->done($request, $thanks);
        }

        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email:filter', 'max:191'],
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address, like name@example.com.',
        ]);
        $email = mb_strtolower(trim($data['email']));

        $subscriber = NewsletterSubscriber::where('email', $email)->first();
        $isNew = !$subscriber || $subscriber->unsubscribed_at;

        if (!$subscriber) {
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'user_id' => optional(User::where('email', $email)->first())->id,
            ]);
        } elseif ($subscriber->unsubscribed_at) {
            $subscriber->update(['unsubscribed_at' => null]);
        }

        if ($isNew) {
            try {
                Mail::to($email)->queue(new NewsletterWelcomeMail($subscriber));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Same message whether or not the address was already subscribed, so the form
        // can't be used to find out who is on the list.
        return $this->done($request, $thanks);
    }

    public function confirmUnsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        return view('newsletter.unsubscribe', ['subscriber' => $subscriber, 'done' => (bool) $subscriber->unsubscribed_at]);
    }

    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();
        if (!$subscriber->unsubscribed_at) {
            $subscriber->update(['unsubscribed_at' => now()]);
        }

        return view('newsletter.unsubscribe', ['subscriber' => $subscriber, 'done' => true]);
    }

    private function done(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect(url()->previous() . '#newsletter')->with('newsletter_success', $message);
    }
}
