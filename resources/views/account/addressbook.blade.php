@extends('layout.front.app')
@section('content')   
    <div class="container">
        <div class="flex account mt-3 mb-3"> 
            <div class="col1">
                @include('layout.front.sidebar')
            </div>
            <div class="col2">
                <div class="card no-border pad-2 bg-grey">
                    <div class="card-heading">
                        <h2>Address Book</h2>
                        <a  href="{{ route('addressbook.create') }}" class="btn btn-primary">Add address</a>
                    </div>
                    <div class="address-wrapper">
                        @if(session('success'))
                            <div class="alert alert-success mt-1 w-12">{{ session('success') }}</div>
                        @endif
                        @if(session('danger'))
                        <div class="alert alert-warning mt-1 w-12">{{ session('danger') }}</div>
                    @endif
                    
                        
                        <table class="table table-address bordered bg-white p-20">
                            <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Reciept Name</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Phone Number</th>                                      
                                        <th>Action</th>
                                    </tr>
                            </thead>
                            <tbody>                                     
                                @foreach ($addressBooks as $address) 
                                    <tr class = "bg-grey-hover @if($address->status == 1) address-default  @endif">
                                        <td class="text-align-center address-type">                                        
                                            @if($address->type == 0)
                                              {{ "Home"}}
                                            @else 
                                              {{ "Business" }}
                                            @endif
                                        </td>
                                        <td>{{ $address->reciept_name }}</td>
                                        <td>{{ $address->reciept_email}}</td>
                                        <td>{{ $address->fullAddress() }}</td>
                                        <td>{{ $address->reciept_number }}</td>                                           
                                        <td>
                                            <ul class="address-ul">
                                                <li>
                                                    <span class="tbl-action" onclick="submitDefault('{{ route('addressbook.default', [$address]) }}')">
                                                        <i class="far fa-hand-pointer"></i>
                                                    </span>
                                                </li>
                                                <li>
                                                    <a href="{{ route('addressbook.edit',[$address]) }}">
                                                        <span class="tbl-action">
                                                            <i class="fas fa-pen"></i>
                                                        </span>
                                                    </a>                                                    
                                                </li>
                                                <li>
                                                    <span  class="tbl-action" onclick="submitDelete('{{ route('addressbook.destroy', [$address]) }}')">
                                                        <i class="far fa-trash-alt"></i>
                                                    </span>
                                                </li>
                                                    <form id="form-default"  method="post">
                                                        @csrf @method('put')                                                    
                                                    </form>
                                                
                                                    <form id="form-delete"   method="post">
                                                        @csrf @method('delete')                                                             
                                                    </form>
                                                   
                                               
                                            </ul>      
                                        </td>
                                    </tr>       
                                @endforeach                                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> 
        </div>
    </div>
@endsection

<script>
 
    function submitDefault(url){
        const form = document.getElementById('form-default');
        form.setAttribute("action",url);      
        form.submit()
    }

    function submitDelete(url){
        const form = document.getElementById('form-delete');
        form.setAttribute("action",url);     
        form.submit()
    }
    

</script>