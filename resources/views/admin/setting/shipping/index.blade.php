@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Shipping Methods</h1>
</div>

    <div class="setting-content m-t-2">
    
        <div class="panel-table w-12">
            <div class="panel-header"> 
              
            </div>
            <div class="toolbar justify-content-space-between action-toolbar hidden"> 
              
                  
                    <label class="title selected-items">2 item Selected</label>
                    <div class="btn-action"> 
                        <form id="selected-update-status" action="{{route('admin.shipping.method.selected.update.status')}}" method="post">
                            @csrf
                            @method('put')                      
                            <div class="select-option">
                                <select name="status" id="method-status">
                                    <option value="">Change status to..</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <span onclick="selectedUpdateStatus()" class="btn btn-light">Change</span>
                            </div>  
                        </form> 
                                                   
                        <span  onclick="selectedDelete()" class="btn btn-light"><i class="fas fa-trash"></i></span>
                        <span  onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
    
                    </div>
              
            </div> 
            <div class="toolbar justify-content-space-between  default-toolbar"> 
                    <form id="formSearch" action="{{ route('admin.shipping.method.search') }}" method="get">                   
                        <div class="search-input"> 
                            <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                           
                            <input type="text" placeholder="Search" name="keyword" value="{{ $keyword ?? '' }}">
                            <a href="{{ route('admin.shipping.method') }}"><span class="icon-right" {{$keyword ?? 'hidden'}}><i class="fa fa-times"></i></span></a>
                        </div>                     
                    </form>
                   
    
                    <a href="{{ route('admin.shipping.method.create') }}" class="btn btn-primary mr-2">                   
                         Create new Method
                    </a>
            </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="column-1">                   
                                 <div class="checkbox">
                                    <input type="checkbox" id="parentCheckbox" name="checkbox">                             
                                 </div>
                             </th>
                            <th>Method</th>
                            <th>Description</th>
                            <th>Charge</th>                                        
                            <th>Status</th>
                            <th class="column-action"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                            @if($shipping_methods->count() == 0)
                            <tr>
                                <td colspan="6"><h3 class="block text-center">No Record Found</h3></td>
                            </tr>
                            @endif
                            @foreach ($shipping_methods as $method)
                                <tr>
                                    <td class="column-1">
                                    
                                            <div class="checkbox">
                                                    <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{$method->encryptedId()}}">
                                            </div>  
                                                                
                                    </td>
                                    <td>{{ $method->name }}</td>
                                    <td>{{ $method->description }}</td>
                                    <td>@money($method->amount)</td>
                                    <td><span class="{{$method->status == 1 ? 'active' : 'inactive' }}">{{ $method->status() }}</span></td>
                                    <td class="column-action"> 
                                        <div class="table-action">
                                            <ul>  
                                                <li>                          
                                                    <a href="{{route('admin.shipping.method.edit', [$method->encryptedId()])}}">
                                                        <span class="span">
                                                            <i class="fas fa-pen"></i>  
                                                        </span>                                                                           
                                                    </a>
                                                </li> 
                                                @if($method->status == 0)                                             
                                                    <li>
                                                        <form  action="{{route('admin.shipping.method.update.status', [$method->encryptedId(), 1])}}" method="post">
                                                            @csrf
                                                            @method('put')
                                                            <button href="#" class="span">
                                                                <span>
                                                                    <i class="fa-solid fa-toggle-off"></i>
                                                                </span>                                                                           
                                                            </button>
                                                        </form>
                                                    
                                                    </li>     
                                                @else
                                                    <li>
                                                        <form  action="{{route('admin.shipping.method.update.status', [$method->encryptedId(), 0])}}" method="post">
                                                            @csrf
                                                            @method('put')
                                                            <button href="#" class="span">
                                                                <span>
                                                                    <i class="fa-solid fa-toggle-on"></i>
                                                                </span>                                                                           
                                                            </button>
                                                        </form>
                                                    
                                                    </li>   
                                                @endif
                                                                                          
                                                <li>
                                                    <form action="{{route('admin.shipping.method.destroy', [$method->encryptedId()])}}" method="post">
                                                        @csrf
                                                        @method('delete')
                                                        <button href="#" class="span">
                                                            <span>
                                                                <i class="fas fa-trash"></i>  
                                                            </span>                                                                           
                                                        </button>
                                                    </form>
                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>  
                            @endforeach
                  
                    </tbody>
                </table>    
        </div>


    </div>


<form id="selected-delete-form" action="{{route('admin.shipping.method.selected.destroy')}}" method="post">
    @csrf
    @method('delete')  
</form>


<script>
    function selectedDelete()
    {
        const form =  document.getElementById('selected-delete-form');
    
        const methods = document.querySelectorAll('.childCheckbox');

        methods.forEach(element => {
            if(element.checked){
                element.type = 'hidden';
                form.append(element) 
            }
                   
        });

        form.submit();
    }

    function selectedUpdateStatus()
    {
        const form =  document.getElementById('selected-update-status');
    
        const methods = document.querySelectorAll('.childCheckbox');

        methods.forEach(element => {
            if(element.checked){
                element.type = 'hidden';
                form.append(element) 
            }      
        });
      
        form.submit();
    }
</script>


@endsection