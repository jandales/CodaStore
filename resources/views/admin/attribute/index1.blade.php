{{-- @extends('layout.admin')

@section('content') 

<h1>Attributes</h1>

  
    <div class="panel m-t-2 w-12">
        <div class="panel-header">           
          
            <div class="justify-content-space-between">
                <p>Manage Attributes</p>   

               <div class="inline">                       
                    <ul class="ul-dropdown">
                        <li><a class="btn btn-default">:</a>
                            <ul class="ul-dropdown-list"> 
                                <li><a href="/admin/products/categories"><i class="fa fa-plus"></i><span class="ml-1">Category</span></a></li>                            
                                <li><a href="/admin/products/variants"><i class="fa fa-plus"></i><span class="ml-1">Manage Varaints</span></a></li>                                
                            </ul>
                        </li>
                    </ul>
               </div>
            </div>           
        </div>
        <div class="panel-body"> 
                    @if (session('error'))
                        <div class="alert alert-danger"> {{ session('error')}} </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success"> {{ session('success')}} </div>
                    @endif

                    @error('value')
                    <div class="alert alert-danger"> {{ $message }} </div>
                    @enderror
                 
                <table class="table table-attributes">
                    <thead>
                    <tr>                        
                        <th class="w-3">Name</th>
                        <th>Value</th>                        
                        <th>Delete</th> 
                    </tr>
                </thead>
                <tbody>
                    <tr class="tr-input">
                        <form action="{{route('attributes.store')}}" method="post">
                            @csrf
                        <td><select name="variant_id">
                            <option value="0">Select Variants</option>
                            @foreach ($variants as $variant)
                                <option value="{{$variant->id}}">{{$variant->name}}</option>
                             
                            @endforeach
                             
                        </select></td>
                        <td> 
                            <input type="text" name="value">
                            <p class="pWarning">Please put a comma every attribute</p>
                         </td>
                        <td>     
                                <button>Add</button>
                           
                          
                        </td>
                    </form>
                    </tr>
                      
                    @foreach ($variants as $variant)
                    <tr>
                        <td>{{ $variant->name }}</td>
                        <td class="w-9 relative">
                            
                                    
                                <ul class="variant-value">
                                    @foreach ($variant->attributes as $attribute)
                                    <li> 
                                        <form action="{{ route('attributes.destroy', [$attribute]) }}" method="post">
                                            @csrf
                                            @method('delete')
                                            <a  class="attribute-value mr-2"><span class="attribute-delete"><i class="fa fa-times"></i></span>  {{ $attribute->value }}</a>
                                        </form> 
                                    </li>
                                    @endforeach
                                </ul>  
                                                 

                           
                        </td>
                        <td>
                            <form action="{{ route('attributes.delete', [$variant->id ]) }}" method="post">
                                @csrf
                                <button class="btn-danger">Delete</button>
                            </form>
                          
                        </td>
                    </tr>      
                             
                    @endforeach
                   
                </tbody>
                </table>
                                     
        
        </div>
    </div>


    <script>

            const attributeDelete = document.querySelectorAll('.attribute-delete');
            
            attributeDelete.forEach(attrDelete => {
                attrDelete.addEventListener('click', function(){
                    let form = attrDelete.closest('form');
                    event.preventDefault()               
                    form.submit();
                })
            })

    </script>
  

   
   





@endsection --}}