@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Inbox</h1>  
</div>

@if(session('success'))
<br><div class="alert alert-success">
<label for="">  {{ session('success') }}</label>
<span class="remove-alert"><i class="fa fa-times"></i></span>
</div>    
@endif
@if(session('error'))
<br><div class="alert alert-error">             
    <label for="">  {{ session('error') }}</label>
    <span class="remove-alert"><i class="fa fa-times"></i></span>
</div>
@endif 

<div class="row" >
   
    <div class="panel-table m-t-2 w-12">
        <div class="flex">
            <div class="inbox-list">
                @foreach ($messages as $message)
                    <a href="{{ route('admin.inbox.show',[$message['id']]) }}">
                        <div class="inbox-message-item">
                            <label for="" class="{{ $message['is_read'] == false ? 'notread' : '' }}">{{$message['subject']}}</label>
                            <div class="flex space-between mt-1">
                                <label for="" class="{{ $message['is_read'] == false ? 'notread' : '' }}">from: {{ $message['from_email']}}</label>
                                <span class="{{ $message['is_read'] == false ? 'notread' : '' }}">{{ diffForHumans($message['sent_at']) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="inbox-content-wrapper">
                @if ($content == null)
                    <h1>Mail Trap APi</h1>
                @else

                    <div class="inbox-header">
                        <h2>{{ $inbox['subject']}}</h2>

                        <div class="flex gap20">
                            <form action="{{ route('admin.inbox.unread',[$id])}}" method="post" class="mb-2">
                                @csrf
                                @method('patch')
                                <button class="btn btn-primary right">Unread</button>
                            </form>                           
                            <form action="{{ route('admin.inbox.destroy',[$id])}}" method="post" class="mb-2">
                                @csrf
                                @method('delete')
                                <button class="btn btn-danger right">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="inbox-details">
                        <div class="flex flex-column">
                            <label for="">From: {{ $inbox['from_email']}}</label>
                            <label for="">To: {{ $inbox['to_email']}}</label>
                        </div>
                        <span>{{ $inbox['sent_at'] }}</span>
                    </div>
            
                        
                        <br>
                        <div class="clearfix w-12">
                            {!! $content !!}
                        </div>
                    
                   
                @endif
               
            </div>
        </div>
        
      
      
         
     
    </div>

   
</div>






@endsection