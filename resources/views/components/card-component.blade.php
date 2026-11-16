<h2 class="checkout-section-title">Payment</h2>
<span class="block mt10 checkout-hint"><i class="fas fa-lock" aria-hidden="true"></i> All transactions are secure and encrypted.</span>
<div class="credit-card mt-1">
    <div class="credit-card-header">
        <span>Credit or debit card</span>
        <button type="button" id="payment-option-change" class="change">Use a saved card</button>
        {{-- <div class="flex gap10">
            <span><i class="fas fa-credit-card"></i></span>
            <span><i class="fas fa-credit-card"></i></span>
            <span><i class="fas fa-credit-card"></i></span>
        </div> --}}
    </div>
    <div class="credit-card-body">

        <div class="form-block">
            <label for="card_name" class="text-sm">Name on card</label>
            <input type="text" id="card_name" autocomplete="cc-name" name="card_name"  validator-input="card_name" value="{{ $card->card_name ?? '' }}"/>
            <small class="validator-text" validator-for="card_name">Enter the name on your card</small>
        </div> 

        <div class="form-block">
            <label for="card_number" class="text-sm">Card details</label>
            <div class="input-custom" validator-input="form-inline-error">
                <div class="inline-form-group">
                    <input type="text" id="card_number" inputmode="numeric" autocomplete="cc-number" aria-label="Card number" class="card-number w-6" validator-input="card_number" name="card_number" placeholder="Card number" value="{{ $card->card_number ?? '' }}">
                    <input type="text" inputmode="numeric" autocomplete="cc-exp" aria-label="Expiry date" class="expired-date w-3" name="card_expire_date"  validator-input="card_expire_date" placeholder="MM/YY" value="{{ $card->card_expire_date ?? '' }}"/>
                    <input type="text" id="card_cvc" inputmode="numeric" autocomplete="cc-csc" aria-label="Security code" class="w-3" name="card_cvc"  validator-input="card_cvc" value="{{ $card->card_cvc ?? '' }}" placeholder="CVC"/>
                 </div>
               
            </div>
            @error('card_number')
                <small class="validator-text" validator-for="form-inline-error">Enter a valid card number</small>
            @enderror
          


        </div>
   
 
    
    </div>
     
       
    
    
   

</div>