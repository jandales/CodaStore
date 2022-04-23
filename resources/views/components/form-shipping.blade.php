<div id="form-shipping" class="form-shipping">
    <div class="flex gap10 mt-1">
        <div class="form-block w-6">
            <label for="contact" class="text-sm">FIRST NAME</label>
            <input type="text" validator-input="firstname" name="firstname" value="{{ $address->firstname ?? ''}}"/>
            <small class="validator-text" validator-for="firstname">Please Enter Card Name</small>
        </div>
        <div class="form-block w-6">
            <label for="contact" class="text-sm">LAST NAME</label>
            <input type="text" name="lastname" validator-input="lastname" value="{{ $address->lastname ?? '' }}"/>
            <small class="validator-text" validator-for="lastname">Please Enter Card Name</small>
        </div>
    </div>   
    <div class="flex gap10 m-t-1">
        <div class="form-block w-3">
            <label for="contact" class="text-sm">COUNRTRY</label>
            <input type="text" name="country" validator-input="country" value="{{ $address->country ?? ''}}"/>
            <small class="validator-text" validator-for="country">Please Enter Card Name</small>
        </div>
        <div class="form-block w-3">
            <label for="contact" class="text-sm">REGION</label>
            <input type="text" name="region" validator-input="region" value="{{ $address->region ?? ''}}"/>
            <small class="validator-text" validator-for="region">Please Enter Card Name</small>
        </div>

        <div class="form-block w-3">
            <label for="contact" class="text-sm">ZIPCODE</label>
            <input type="text" name="zipcode" validator-input="zipcode" value="{{$address->zipcode ?? '' }}"/>
            <small class="validator-text" validator-for="zipcode">Please Enter Card Name</small>
        </div>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">STREET</label>
        <input type="text" name="street" validator-input="street" value="{{ $address->street ?? '' }}"/>
        <small class="validator-text" validator-for="street">Please Enter Card Name</small>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">CITY</label>
        <input type="text" name="city" validator-input="city" value="{{ $address->city ?? '' }}"/>
        <small class="validator-text" validator-for="city">Please Enter Card Name</small>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">PHONE NUMBER</label>
        <input type="number" name="phone" value="{{ $address->phone ?? '' }}"/>
        <small class="validator-text" validator-for="phone">Please Enter Card Name</small>
    </div>
</div>