@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Inbox</h1>  
</div>

<div class="row" >
    <div class="panel-table m-t-2 w-12">
        <div class="panel-header">             
            @if(session('success'))
                <br><div class="alert alert-success">
                  <label for="">  {{ session('success') }}</label>
                  <span class="remove-alert"><i class="fa fa-times"></i></span>
                </div>    
            @endif
            @if(session('error'))
                <br><div class="alert alert-success">             
                    <label for="">  {{ session('error') }}</label>
                    <span class="remove-alert"><i class="fa fa-times"></i></span>
                </div>
            @endif 
        </div>
        
        <div class="flex">
            <div class="inbox-list">
                @foreach ($messages as $message)
                    <a href="{{ route('admin.inbox.show',[$message['id']]) }}">
                        <div class="inbox-message-item {{ $message['is_read'] == false ? 'notread' : '' }}">
                            <label for="">{{$message['subject']}}</label>
                            <div class="flex space-between">
                                <label for="">from: {{ $message['from_email']}}</label>
                                <span>{{ diffForHumans($message['sent_at']) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="inbox-content">
                @if ($content == null)
                    <h1>Mail Trap APi</h1>
                @else
                    <div>
                        <form action="{{ route('admin.inbox.destroy',[$id])}}" method="post">
                            @csrf
                            @method('delete')
                            <button>Delete</button>
                        </form>
                    </div>
                @endif
                {!! $content !!}
            </div>
        </div>
        
      
      
         
     
    </div>

   
</div>






@endsection