<?php 
namespace App\Services;

use App\Models\UserPaymentOption;

class UserPaymentOptionServices {

    public function store($request)
    {      
       return UserPaymentOption::create([
            'card_name' => $request->card_name,
            'card_number' => $request->card_number,
            'card_expire_date' => $request->card_expire_date,
            'card_cvc' => $request->card_cvc,
            'user_id' => auth()->user()->id,
            'status' => UserPaymentOption::get()->count() == 0 ? 1 : 0,
        ]);
           
    }

    public function update($request,UserPaymentOption $option)
    {    
            $option->card_name = $request->card_name;
            $option->card_number = $request->card_number;
            $option->card_expire_date = $request->card_expire_date;
            $option->card_cvc = $request->card_cvc;
            $option->save();
            return $option;        
    }
  
    public function updateStatus(UserPaymentOption $option)
    {         

        if ($option->status == 1) return false;
         // update default option to false
        $default_option = auth()->user()->defaultPayment();
        if($default_option){
            self::optionStatus($default_option, 0);
        }       
        //set new default option
        self::optionStatus($option, 1);

        return $option;
    }

    public function optionStatus(UserPaymentOption $option, $status)
    {
        $option->status = $status;
        $option->save();
       
    }
}