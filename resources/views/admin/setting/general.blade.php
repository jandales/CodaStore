@extends('layout.admin')
@section('content') 

<div class="page-title">
    <h1>General Settings</h1>
</div>
@if(session('success'))
    <div class="alert alert-success m-t-1">{{ session('success') }}</div>                
@endif 
<div class="setting-content m-t-2">    
    <div class="panel">            
        <div class="panel-body">
            <form id="form" method="POST"  action="{{route('admin.setting.general.update',[$general_setting])}}">
                @csrf 
                @method('put')
            <div class="form-block">
                <label for="name">Site Name</label>
                <input type="text" name="site_name" value="{{ $general_setting->site_name }}">                               
            </div>
            @error('site_name')
                 <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror

            <div class="form-block">
                <label for="name">Tagline</label>
                <input type="text" name="tag_line" value="{{ $general_setting->tag_line }}">                             
            </div>
            @error('tag_line')
             <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror    

            <div class="form-block">
                <label for="name">Website Address (URL)</label>
                <input type="text" name="site_url" value="{{ $general_setting->site_url }}">                             
            </div>
            @error('site_url')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror   

            <div class="form-block">
                <label for="name">Email Address</label>
                <input type="text" name="site_email" value="{{ $general_setting->site_email }}">                        
                @error('site_email')
                    <div class="alert alert-danger mt10 ">{{ $message }}</div>
                @enderror  
            </div>
          
            <div class="form-block">
                <label for="name">Timezone</label>
                <select name="timezone" id="">
                    @foreach (timezone_list() as $item)
                        <option value="{{ $item->value }}" {{$item->value == $general_setting->timezone ? 'selected' : ''}} >{{ $item->display}}</option>
                    @endforeach  
                </select>   
                @error('timezone')
                    <div class="alert alert-danger mt10 ">{{ $message }}</div>
                @enderror                        
            </div>

            <div class="form-block">
                <label for="name">Date Format</label>
                <select name="date_format" id="">
                    @foreach (list_date_format() as $item)
                        <option value="{{ $item->code }}" {{ $general_setting->date_format == $item->code  ? 'checked' : ''}} >{{ $item->format . " - " . $item->code }}</option>
                    @endforeach  
                </select>   
                @error('date_format')
                    <div class="alert alert-danger mt10 ">{{ $message }}</div>
                @enderror                        
            </div>

            <div class="form-block">
                <label for="name">Time Format</label>
                <select name="time_format" id="">
                    @foreach (list_time_format() as $item)
                        <option  value="{{ $item->code }}"  {{ $general_setting->time_format == $item->code  ? 'checked' : ''}}>{{ $item->format . " - " . $item->code }}</option>
                    @endforeach  
                </select>   
                @error('time_format')
                    <div class="alert alert-danger mt10 ">{{ $message }}</div>
                @enderror                        
            </div>

            <div class="flex gap10">
                <button id="btnsave" type="create" class="btn btn-primary">Save Changes</button>                  
            </div>
            
        </form>
        </div>
    </div>



</div>

@endsection