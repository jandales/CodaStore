<div class="toolbar justify-content-space-between action-toolbar hidden"> 
    <label class="title selected-items">0 item Selected</label>
    <div class="btn-action"> 
        @can('delete', $list[0])                  
             <span  id="btn-delete-attribute" data-url = {{ route('admin.attributes.destroy.selected') }} class="btn btn-light"><i class="fas fa-trash"></i></span>
        @endcan
        <span  id="clear-selection" class="btn btn-light"><i class="fas fa-times"></i></span> 
    </div>
</div> 
<div class="toolbar justify-content-space-between default-toolbar">                        
    <label class="title">List of Attributes</label>
    <form action="{{ route('admin.attributes.search') }}" method="get">
        <div class="search-input"> 
            <span class="icon-right"><i class="fas fa-search"></i></span>                                                        
            <input  class="txtsearch" type="text" placeholder="Search"  name="keyword" value="">
            <span data-url="{{ route('admin.attributes') }}" class="icon-left close-search"><i class="fas fa-times search-close-icon {{ $keyword == null ? 'hidden' : '' }}"></i></span> 
        </div>  
    </form>  
</div>
    <table class="table">
        <thead>
        <tr> 
            <th width="50px"><div class="checkbox"><input type="checkbox" id="parentCheckbox" name="checkbox" ></div></th>                        
            <th>Name</th>
            <th>Description</th>                           
            <th class="column-action"></th> 
        </tr>
    </thead>
    <tbody>
        @if ($list->count() == 0 )
            <tr> <td colspan="7" ><label class="text-center">No found Record</label></td> </tr>
        @endif 
        @foreach ($list as $item)
            <tr>
                <td><div class="checkbox"><input type="checkbox"  class="childCheckbox" name="selected[]"  value="{{ $item->slug }}"> </div></td>
                <td><a class="item-name">{{ $item->name }}</a></td>
                <td>{{ $item->description }}</td>                   
                <td class="column-action">
                    <div class="table-action">
                        <ul>   
                            <li><a href="{{ route('admin.attributes.edit',[$item->slug]) }}"><span class="span"><i class="fas fa-pen"></i></span></a></li>
                            @can('delete', $item)
                                <li>
                                    <form action="{{ route('admin.attributes.destroy', [$item->slug])}}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button class="span"><i class="fas fa-trash"></i></button>
                                    </form>
                                
                                </li>   
                            @endcan
                        </ul>
                    </div>
                </td>
        </tr>
        @endforeach
       
                         
    </tbody>      
    </table> 