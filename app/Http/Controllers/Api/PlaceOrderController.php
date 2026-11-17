<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlaceOrderController extends Controller
{
    /**
     * Placing an order needs a confirmed online payment, which the API does not support yet.
     * Before this change the endpoint created orders marked "paid" without charging anything.
     */
    public function store()
    {
        return response()->json([
            'message' => 'Online payment is required. Please complete checkout on the website.',
        ], 422);
    }
}
