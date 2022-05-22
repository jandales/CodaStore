<h2 class="uppercase">Payment</h2>
<span class="block mt10">All Transactiion are secure and encrypted</span>
<div class="credit-card mt-1">
    <div class="credit-card-header">
        <span>Credit Card Payment</span>
        <span id="payment-option-change"  class="change">Change</span>
        {{-- <div class="flex gap10">
            <span><i class="fas fa-credit-card"></i></span>
            <span><i class="fas fa-credit-card"></i></span>
            <span><i class="fas fa-credit-card"></i></span>
        </div> --}}
    </div>
    <div class="credit-card-body">

        <div class="form-block">
            <label for="contact" class="text-sm">Card Name</label>
            <input type="text" name="card_name"  validator-input="card_name" value="{{ $card->card_name ?? '' }}"/>
            <small class="validator-text" validator-for="card_name">Please Enter Card Name</small>
        </div> 

        <div class="form-block">
            <label for="contact" class="text-sm">Card Data</label>
            <div class="input-custom">
                <div class="inline-form-group">
                    <input type="text" class="card-number w-6" validator-input="card_number" name="card_number" placeholder="xxxx-xxxx-xxxx-xxxx" value="{{ $card->card_number ?? '' }}">
                    <input type="text" class="expired-date w-3" name="card_expire_date"  validator-input="card_expire_date" placeholder="MM/YY" value="{{ $card->card_expire_date ?? '' }}"/>
                    <input type="text" id="card_cvc" class="w-3" name="card_cvc"  validator-input="card_cvc" value="{{ $card->card_cvc ?? '' }}"/>
                 </div>
               
            </div>
            @error('card_number')
                <small class="validator-text" validator-for="card_number">Please Enter Card Number</small>
            @enderror
            @error('card_expire_date')
                <small class="validator-text" validator-for="card_expire_date">Please Enter Card Number</small>
            @enderror
            @error('card_cvc')
                <small class="validator-text" validator-for="card_cvc">Please Enter security Number</small>
            @enderror


        </div>
   
 
    
    </div>
     
       
    
    
   

</div>