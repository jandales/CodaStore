<div id="form-shipping" class="form-shipping @auth  hidden  @endauth">
    <div class="flex gap10 mt-1">
        <div class="form-block w-6">
            <label for="bill-billing_firstname" class="text-sm">First name</label>
            <input id="bill-billing_firstname" type="text" name="billing_firstname" validator-input="billing_firstname" value="{{ $address->firstname ?? ''}}"/>
            <small class="validator-text" validator-for="billing_firstname">This field is required</small>
        </div>
        <div class="form-block w-6">
            <label for="bill-billing_lastname" class="text-sm">Last name</label>
            <input id="bill-billing_lastname" type="text" name="billing_lastname" validator-input="billing_lastname" value="{{ $address->lastname ?? '' }}"/>
            <small class="validator-text" validator-for="billing_lastname">This field is required</small>
        </div>
    </div>   
    <div class="flex gap10 m-t-1">
        <div class="form-block w-3">
            <label for="bill-billing_country" class="text-sm">Country</label>
            <input id="bill-billing_country" type="text" name="billing_country" validator-input="billing_country" value="{{ $address->country ?? ''}}"/>
            <small class="validator-text" validator-for="billing_country">This field is required</small>
        </div>
        <div class="form-block w-3">
            <label for="bill-billing_region" class="text-sm">Region / province</label>
            <input id="bill-billing_region" type="text" name="billing_region" validator-input="billing_region" value="{{ $address->region ?? ''}}"/>
            <small class="validator-text" validator-for="billing_region">This field is required</small>
        </div>

        <div class="form-block w-3">
            <label for="bill-billing_zipcode" class="text-sm">ZIP code</label>
            <input id="bill-billing_zipcode" type="text" name="billing_zipcode" validator-input="billing_zipcode" value="{{$address->zipcode ?? '' }}"/>
            <small class="validator-text" validator-for="billing_zipcode">This field is required</small>
        </div>
    </div>
    <div class="form-block">
        <label for="bill-billing_street" class="text-sm">Street address</label>
        <input id="bill-billing_street" type="text" name="billing_street" validator-input="billing_street" value="{{ $address->street ?? '' }}"/>
        <small class="validator-text" validator-for="billing_street">This field is required</small>
    </div>
    <div class="form-block">
        <label for="bill-billing_city" class="text-sm">City</label>
        <input id="bill-billing_city" type="text" name="billing_city" validator-input="billing_city" value="{{ $address->city ?? '' }}"/>
        <small class="validator-text" validator-for="billing_city">This field is required</small>
    </div>
    <div class="form-block">
        <label for="bill-billing_phone" class="text-sm">Phone number</label>
        <input id="bill-billing_phone" type="number" name="billing_phone" validator-input="billing_phone" value="{{ $address->phone ?? '' }}"/>
        <small class="validator-text" validator-for="billing_phone">This field is required</small>
    </div>
</div>