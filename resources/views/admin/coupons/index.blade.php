@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Coupons</h1>
   
</div>
@if(session('success'))
<br>
<div class="alert alert-success">{{ session('success') }}</div>    
@endif

<div class="row"> 
    <div class="panel-table m-t-2 w-12">        
        <div class="toolbar justify-content-space-between action-toolbar hidden"> 
            <label class="title selected-items">2 item Selected</label>
            <div class="btn-action">                                          
                <span id="coupon-selected-destroy" data-id="{{ route('admin.coupon.destroy.selected') }}" class="btn btn-light"><i class="fas fa-trash"></i></span> 
                <span id="clear-selection" class="btn btn-light"><i class="fas fa-times"></i></span> 
            </div>        
        </div> 
        <div class="toolbar justify-content-space-between default-toolbar"> 
            <form id="formSearch" action="{{ route('admin.coupons.search') }}" method="get">  
                <div class="search-input"> 
                    <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                         
                    <input type="text" placeholder="Search" name="keyword" value="{{ $keyword ?? '' }}">                   
                    <a href="{{route('admin.coupons')}}" class="{{ $keyword ?? 'hidden'}}"><span class="icon-right"><i class="fas fa-times"></i></span></a>
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
                    <form id="coupon-form"  method="post">
                        @csrf
                        @method('delete')
                        @if ( $coupons->count() == 0 )
                            <tr> <td colspan="6" ><label class="text-center">No found Record</label></td> </tr>
                        @endif
                        @foreach ($coupons as $coupon)
                            <tr>
                                <td class="tr-checkbox">
                                    <div class="checkbox">
                                        <input  type="checkbox" class="childCheckbox" name="selected[]"   value="{{ $coupon->encryptedId() }}">
                                    </div>                             
                                </td>
                                <td><a href="{{ route('admin.coupon.show',[ $coupon->encryptedId() ]) }}"  >{{ $coupon->name }}</a></td>
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
                                                <a href="{{ route('admin.coupon.edit',[$coupon->encryptedId()]) }}">
                                                    <span class="span">
                                                        <i class="fas fa-pen"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>
                                            <li>                          
                                                <a href="{{ route('admin.coupon.show',[ $coupon->encryptedId() ]) }}">
                                                    <span class="span">
                                                        <i class="fas fa-eye"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>    
                                                <li>
                                                    <span  data-url={{ route('admin.coupon.destroy',[$coupon->encryptedId()])}} class="span coupon-destroy">
                                                            <i class="fas fa-trash"></i>  
                                                    </span>  
                                                </li>
                                            
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </form>               
                </tbody>                
            </table>
            <div class="mt-2 mb-2 right mr10">
                {{ $coupons->links() }}
            </div>
    
    </div>

   
</div>



@endsection