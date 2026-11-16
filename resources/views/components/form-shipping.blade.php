<div id="form-shipping" class="form-shipping">
    <div class="flex gap10 mt-1">
        <div class="form-block w-6">
            <label for="ship-firstname" class="text-sm">First name</label>
            <input id="ship-firstname" type="text" validator-input="firstname" name="firstname" value="{{ $address->firstname ?? ''}}"/>
            <small class="validator-text" validator-for="firstname">This field is required</small>
        </div>
        <div class="form-block w-6">
            <label for="ship-lastname" class="text-sm">Last name</label>
            <input id="ship-lastname" type="text" name="lastname" validator-input="lastname" value="{{ $address->lastname ?? '' }}"/>
            <small class="validator-text" validator-for="lastname">This field is required</small>
        </div>
    </div>   
    <div class="flex gap10 m-t-1">
        <div class="form-block w-3">
            <label for="ship-country" class="text-sm">Country</label>
            <input id="ship-country" type="text" name="country" validator-input="country" value="{{ $address->country ?? ''}}"/>
            <small class="validator-text" validator-for="country">This field is required</small>
        </div>
        <div class="form-block w-3">
            <label for="ship-region" class="text-sm">Region / province</label>
            <input id="ship-region" type="text" name="region" validator-input="region" value="{{ $address->region ?? ''}}"/>
            <small class="validator-text" validator-for="region">This field is required</small>
        </div>

        <div class="form-block w-3">
            <label for="ship-zipcode" class="text-sm">ZIP code</label>
            <input id="ship-zipcode" type="text" name="zipcode" validator-input="zipcode" value="{{$address->zipcode ?? '' }}"/>
            <small class="validator-text" validator-for="zipcode">This field is required</small>
        </div>
    </div>
    <div class="form-block">
        <label for="ship-street" class="text-sm">Street address</label>
        <input id="ship-street" type="text" name="street" validator-input="street" value="{{ $address->street ?? '' }}"/>
        <small class="validator-text" validator-for="street">This field is required</small>
    </div>
    <div class="form-block">
        <label for="ship-city" class="text-sm">City</label>
        <input id="ship-city" type="text" name="city" validator-input="city" value="{{ $address->city ?? '' }}"/>
        <small class="validator-text" validator-for="city">This field is required</small>
    </div>
    <div class="form-block">
        <label for="ship-phone" class="text-sm">Phone number</label>
        <input id="ship-phone" type="tel" name="phone" autocomplete="tel" value="{{ $address->phone ?? '' }}"/>
        <small class="validator-text" validator-for="phone">This field is required</small>
    </div>
</div>