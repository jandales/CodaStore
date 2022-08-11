<?php

namespace App\Http\Controllers;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
  
    public function index()
    {
        return view('paypal');
    }

    public function process(Request $request)
    {
        $provider = new PayPalClient;        
        $provider->setApiCredentials(config('paypal'));     
        $token = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('processSuccess'),
                "cancel_url" => route('processCancel'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "PHP",
                        "value" => "100.00"
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {

            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }

            return redirect()
                ->route('paypal')
                ->with('error', 'Something went wrong.');

        } else {
            return redirect()
                ->route('paypal')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }

     
    }

    public function success(Request $request)
    {

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($request['token']);
        
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $request->session()->put('success','Transaction complete');
                return redirect()->route('paypal');
                   
                  
            } else {
                $request->session()->put('error',$response['message'] ?? 'Something went wrong.');
                return redirect()->route('paypal');
            }

    }

    public function cancel(Request $request)
    {
        return redirect()
            ->route('paypal')
            ->with('error', $response['message'] ?? 'You have canceled the transaction.');
    }


}
