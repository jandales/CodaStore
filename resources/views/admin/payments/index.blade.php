@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Payments</h1>
   
</div>
@if(session('success'))
<br>
<div class="alert alert-success">{{ session('success') }}</div>    
@endif
@if(session('error'))
<br>
<div class="alert alert-danger">{{ session('error') }}</div>    
@endif

<div class="row"> 
    <div class="panel-table m-t-2 w-12">        
        <div class="toolbar justify-content-space-between default-toolbar"> 
            <form id="formSearch" action="{{ route('admin.payments') }}" method="get">  
                <div class="search-input"> 
                    <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                         
                    <input type="text" placeholder="Search order / reference" name="keyword" value="{{ $keyword ?? '' }}">                   
                    <a href="{{ route('admin.payments') }}" class="{{ $keyword ?? 'hidden' }}"><span class="icon-right"><i class="fas fa-times"></i></span></a>
                </div>                     
            </form>  
        </div>

           
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Provider</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                        @if ( $payments->count() == 0 )
                            <tr> <td colspan="7" ><label class="text-center">No found Record</label></td> </tr>
                        @endif
                        @foreach ($payments as $payment)
                            <tr>
                                <td>
                                    @if ($payment->order)
                                        <a class="link-primary" href="{{ route('admin.orders.show', [$payment->order->encryptedId()]) }}">
                                            {{ $payment->order->order_number }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="capitalized">{{ $payment->provider }}</td>
                                <td>{{ $payment->reference_number }}</td>
                                <td>@money($payment->amount)</td>
                                <td><span class="status capitalized {{ $payment->statusColor() }}">{{ $payment->status }}</span></td>
                                <td>{{ $payment->createdAtDate() }}</td>
                                <td width="80px"> 
                                    <div class="table-action">
                                        <ul>  
                                            @if ($payment->order)
                                            <li>                          
                                                <a href="{{ route('admin.orders.show', [$payment->order->encryptedId()]) }}">
                                                    <span class="span">
                                                        <i class="fas fa-eye"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach  
                </tbody>
            </table>
         
            <div class="mt-2 mb-2 right mr10">
                {{ $payments->withQueryString()->links() }}
            </div>
            
     
    </div>

   
</div>




@endsection
