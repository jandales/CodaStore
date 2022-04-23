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
            <div class="form-inline">
                <label for="name">Site Name</label>
                <input type="text" name="site_name" value="{{ $general_setting->site_name }}">                               
            </div>
            @error('site_name')
                 <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror 

            <div class="form-inline">
                <label for="name">Tagline</label>
                <input type="text" name="tag_line" value="{{ $general_setting->tag_line }}">                             
            </div>
            @error('tag_line')
             <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror    

            <div class="form-inline">
                <label for="name">Website Address (URL)</label>
                <input type="text" name="site_url" value="{{ $general_setting->site_url }}">                             
            </div>
            @error('site_url')
                <div class="alert alert-danger mt10">{{ $message }}</div>
            @enderror   

            <div class="form-inline">
                <label for="name">Email Address</label>
                <input type="text" name="site_email" value="{{ $general_setting->site_email }}">                        
            </div>
            @error('site_email')
                <div class="alert alert-danger mt10 ">{{ $message }}</div>
            @enderror  

            <div class="form-inline">
                <label for="name">Timezone</label>
                <select name="timezone" id="">
                    @foreach (timezone_list() as $item)
                        <option value="{{ $item->value }}" {{$item->value == $general_setting->timezone ? 'selected' : ''}} >{{ $item->display}}</option>
                    @endforeach
                    
                 
                </select>                          
            </div>
            @error('timezone')
                 <div class="alert alert-danger mt10 ">{{ $message }}</div>
            @enderror 
         

            <div class="form-inline items-start">
                <label for="name">Date Format</label>
                <div class="date-time-format">
                    @foreach (list_date_format() as $item)
                        <label for="name">
                            <input type="radio" name="date_format" id="date-format" value="{{ $item->code }}" {{ $general_setting->date_format == $item->code  ? 'checked' : ''}}>
                            <span>{{ $item->format }}</span>
                            <code>{{ $item->code }}</code>
                        </label>
                    @endforeach
                </div>                  
            </div>

            <div class="form-inline items-start">
                <label for="name">Time Format</label>
                <div class="date-time-format">
                    @foreach (list_time_format() as $item)
                         <label for="name">
                            <input type="radio" name="time_format" id="time-format" value="{{ $item->code }}"  {{ $general_setting->time_format == $item->code  ? 'checked' : ''}}>
                            <span>{{ $item->format }}</span>
                            <code>{{ $item->code }}</code>
                        </label>  
                    @endforeach
                
    
                </div>
            </div>


            <div class="flex justify-content-end gap10">
                <button id="btnsave" type="create" class="btn btn-primary">Save</button>                  
            </div>
            
        </form>
        </div>
    </div>



</div>

@endsection