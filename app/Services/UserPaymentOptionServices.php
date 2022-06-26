<?php 
namespace App\Services;

use App\Models\UserPaymentOption;

class UserPaymentOptionServices {

    public function getPaymentOptions($user_id)
    {      
        return UserPaymentOption::where('user_id',$user_id)->get();
    }

    public function getPaymentOption($user_id, $id)
    {
       return UserPaymentOption::where(['user_id'=> $user_id, 'id' => $id])->first();
    }

    public function getPaymentOptionByCardNumber($user_id, $card_number)
    {
        return UserPaymentOption::where(['user_id'=> $user_id, 'card_number' => $card_number])->first();
    }



    public function store($request, $user_id)
    {      
       $options_count = Self::getPaymentOptions($user_id)->count();

       return UserPaymentOption::create([
            'card_name' => $request->card_name,
            'card_number' => $request->card_number,
            'card_expire_date' => $request->card_expire_date,
            'card_cvc' => $request->card_cvc,
            'user_id' => $user_id,
            'status' => $options_count == 0 ? 1 : 0,
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
  
    public function updateStatus(UserPaymentOption $option, $user_id)
    {         
       
        if ($option->status == 1) return $option;
         // update default option to false
       
        $default_option = UserPaymentOption::where(['user_id' => $user_id, 'status' => 1])->first();

        if ($default_option) {
            self::optionStatus($default_option, 0);
        }       
        //set new default option
        $option = self::optionStatus($option, 1);

        return $option;
    }

    public function optionStatus(UserPaymentOption $option, $status)
    {
        $option->status = $status;
        $option->save();
        return $option;
       
    }
}