<div id="form-shipping" class="form-shipping @auth  hidden  @endauth">
    <div class="flex gap10 mt-1">
        <div class="form-block w-6">
            <label for="contact" class="text-sm">FIRST NAME</label>
            <input type="text" name="firstname" value="{{ $address->firstname ?? ''}}"/>
        </div>
        <div class="form-block w-6">
            <label for="contact" class="text-sm">LAST NAME</label>
            <input type="text" name="lastname" value="{{ $address->lastname ?? '' }}"/>
        </div>
    </div>   
    <div class="flex gap10 m-t-1">
        <div class="form-block w-3">
            <label for="contact" class="text-sm">COUNRTRY</label>
            <input type="text" name="country" value="{{ $address->country ?? ''}}"/>
        </div>
        <div class="form-block w-3">
            <label for="contact" class="text-sm">REGION</label>
            <input type="text" name="region" value="{{ $address->region ?? ''}}"/>
        </div>

        <div class="form-block w-3">
            <label for="contact" class="text-sm">ZIPCODE</label>
            <input type="text" name="zipcode" value="{{$address->zipcode ?? '' }}"/>
        </div>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">STREET</label>
        <input type="text" name="street" value="{{ $address->street ?? '' }}"/>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">CITY</label>
        <input type="text" name="city" value="{{ $address->city ?? '' }}"/>
    </div>
    <div class="form-block">
        <label for="contact" class="text-sm">PHONE NUMBER</label>
        <input type="number" name="phone" value="{{ $address->phone ?? '' }}"/>
    </div>
</div>