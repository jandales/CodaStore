@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Coupons</h1>
   
</div>
@if(session('success'))
<br>
<div class="alert alert-success">{{ session('success') }}</div>    
@endif

<div class="row" >
  

    <div class="panel-table m-t-2 w-12">        
        <div class="toolbar justify-content-space-between action-toolbar hidden"> 
            <label class="title selected-items">2 item Selected</label>
            <div class="btn-action">                                          
                <span onclick="destroy('{{ route('admin.coupon.destroy.selected') }}')" class="btn btn-light"><i class="fas fa-trash"></i></span> 
                <span onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
            </div>        
        </div> 
        <div class="toolbar justify-content-space-between default-toolbar"> 
            <form id="formSearch" action="{{ route('admin.coupons.search') }}" method="post">
                @csrf
                <div class="search-input"> 
                    <span class="icon-left"></span>                           
                    <input type="text" placeholder="Search" name="search" value="{{ old('search') }}">
                    <a href="{{route('admin.coupons')}}"><span class="icon-right"><i class="fas fa-times"></i></span></a>
                </div>                     
            </form>  
            <a href="{{ route('admin.coupon.create') }}"  class="btn btn-primary mr-2"> Create Coupon</a>
        </div>

           
            <table class="table">
                <thead>
                    <tr>
                        <th class="tr-checkbox">                   
                             <div class="checkbox">

                                <input type="checkbox" id="parentCheckbox" name="checkbox" >
                             
                             </div>
                         </th>
                        <th>Name</th>
                        <th>Discount type</th>
                        <th>Amount</th>
                        <th>Usage / Limit</th>                                        
                        <th>Expiration</th> 
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <form id="form"  method="post">
                        @csrf
                        <input type="hidden" name="_method" value="post">


                        @foreach ($coupons as $coupon)
                        <tr>
                            <td class="tr-checkbox">
                                <div class="checkbox">
                                    <input  type="checkbox" class="childCheckbox" name="selected[]" action="{{ route( 'admin.coupon.destroy', [$coupon] ) }}"  value="{{ $coupon->id }}">
                                </div>                             
                            </td>
                            <td><a href="{{ route('admin.coupon.show',[$coupon]) }}"  >{{ $coupon->name }}</a></td>
                            <td>{{ $coupon->discountType() }}</td>
                            <td>{{ $coupon->amount }}</td>
                            <td> @if($coupon->limit_per_coupon == 0 ) <span class="infinite">&#8734;</span>   @else <span>{{ $coupon->usegeLimit() }}</span> @endif </td>                                              
                            <td>
                                 @if($coupon->date('Y-m-d') == "")
                                     <span class="infinite">&#8734;</span>                                  
                                 @else 
                                    @if ($coupon->expired())
                                         <span class="expired">expired<span> 
                                    @else
                                        <span> {{ $coupon->date('Y-m-d') }}</span>
                                    @endif                                   
                                 @endif
                            </td>                  
                            <td width="100px"> 
                                <div class="table-action">
                                    <ul>  
                                        <li>                          
                                            <a href="{{ route('admin.coupon.edit',[$coupon]) }}">
                                                <span class="span">
                                                    <i class="fas fa-pen"></i>  
                                                </span>                                                                           
                                            </a>
                                        </li>
                                        <li>                          
                                            <a href="{{ route('admin.coupon.show',[$coupon]) }}">
                                                <span class="span">
                                                    <i class="fas fa-eye"></i>  
                                                </span>                                                                           
                                            </a>
                                        </li>    
                                            <li>
                                                <a href="#" id="delete">
                                                    <span onclick="destroy('{{ route('admin.coupon.destroy',[$coupon]) }}')" link ="#" class="span">
                                                        <i class="fas fa-trash"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>
                                           
                                    </ul>
                                </div>
                            </td>
                        </tr>
                            @endforeach
                    </form>
               
                </tbody>
                
            </table>
            
    
    </div>

   
</div>




<script>

function destroy(route)
{
    const form = document.getElementById('form')
    document.querySelector('input[name="_method"]').value= 'delete'
    form.setAttribute('action', route)
    form.submit();
}

</script>


@endsection