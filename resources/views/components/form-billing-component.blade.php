<div id="form-shipping" class="form-shipping @auth  hidden  @endauth">
    <div class="flex gap10 mt-1">
        <div class="form-block w-6">
            <label for="contact" class="text-sm">FIRST NAME</label>
            <input type="text" name="billing_firstname" validator-input="billing_firstname" value="{{ $address->firstname ?? ''}}"/>
            <small class="validator-text" validator-for="billing_firstname">Please Enter Card Name</small>
        </div>
        <div class="form-block w-6">
            <label for="contact" class="text-sm">LAST NAME</label>
            <input type="text" name="billing_lastname" validator-input="billing_lastname" value="{{ $address->lastname ?? '' }}"/>
            <small class="validator-text" validator-for="billing_lastname">Please Enter Card Name</small>
        </div>
    </div>   
    <div class="flex gap10 m-t-1">
        <div class="form-block w-3">
            <label for="contact" class="text-sm">COUNRTRY</label>
            <input type="text" name="billing_country" validator-input="billing_country" value="{{ $address->country ?? ''}}"/>
            <small class="validator-text" validator-for="billing_country">Please Enter Card Name</small>
        </div>
        <div class="form-block w-3">
            <label for="contact" class="text-sm">REGION</label>
            <input type="text" name="billing_region" validator-input="billing_region" value="{{ $address->region ?? ''}}"/>
            <small class="validator-text" validator-for="billing_region">Please Enter Card Name</small>
        </div>

        <div class="form-block w-3">
            <label for="contact" class="text-sm">ZIPCODE</label>
            <input type="text" name="billing_zipcode" validator-input="billing_zipcode" value="{{$address->zipcode ?? '' }}"/>
            <small class="validator-text" validator-for="billing_zipcode">Please Enter Card Name</small>
        </div>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">STREET</label>
        <input type="text" name="billing_street" validator-input="billing_street" value="{{ $address->street ?? '' }}"/>
        <small class="validator-text" validator-for="billing_street">Please Enter Card Name</small>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">CITY</label>
        <input type="text" name="billing_city" validator-input="billing_city" value="{{ $address->city ?? '' }}"/>
        <small class="validator-text" validator-for="billing_city">Please Enter Card Name</small>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">PHONE NUMBER</label>
        <input type="number" name="billing_phone" validator-input="billing_phone" value="{{ $address->phone ?? '' }}"/>
        <small class="validator-text" validator-for="billing_phone">Please Enter Card Name</small>
    </div>
</div>