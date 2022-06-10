<div class="toolbar justify-content-space-between action-toolbar hidden"> 
    <label class="title selected-items">0 item Selected</label>
    <div class="btn-action">                   
      @can('delete', $categories[0])
        <span id="btn-selected-cateory-delete" data-id="{{ route('admin.categories.selected.destroy') }}" class="btn btn-light"><i class="fas fa-trash"></i></span>
      @endcan
       
        <span id="clear-selection" class="btn btn-light"><i class="fas fa-times"></i></span> 
    </div>
</div> 

<div class="toolbar default-toolbar"> 
    <form action="{{ route('admin.categories.seacrh') }}" method="get">
        <div class="search-input"> 
            <span class="icon-right"><i class="fas fa-search"></i></span>                                                        
            <input  class="txtsearch" type="text" placeholder="Search"  name="keyword" value="{{ $keyword ?? ''}}">
            <span data-url="{{ route('admin.categories') }}" class="icon-left close-search"><i class="fas fa-times search-close-icon {{ $keyword == null ? 'hidden' : '' }}"></i></span> 
        </div>  
    </form>                       
           
</div>

    <table class="table">
        <thead>
        <tr>  
            <th  width="50px">                   
                <div class="checkbox">
                            <input type="checkbox" id="parentCheckbox" name="checkbox" >
                </div>
            </th>                      
            <th>Name</th> 
            <th>Description</th>                                         
            <th>Slug</th> 
            <th class="column-action"></th> 
        </tr>
        </thead>
        <tbody> 
               @if ($categories->count() == 0 )
                    <tr> <td colspan="7" ><label class="text-center">No found Record</label></td> </tr>
              @endif                       
              @foreach ($categories as $category)
                    <tr>
                        <td>
                            <div class="checkbox">
                                <input type="checkbox"  class="childCheckbox" name="selected[]"  value="{{$category->id}}">
                            </div>
                        </td>  
                        <td>{{$category->name}}</td>
                        <td>{{$category->description}}</td>
                        <td>{{$category->slug}}</td>
                        <td class="column-action">                            
                            <div class="table-action">
                                <ul>   
                                    <li>                                   
                                        <a href="{{ route('categories.edit',[$category->slug])}}">
                                            <span class="span">
                                                <i class="fas fa-pen"></i>  
                                            </span>                                                                           
                                        </a>
                                    </li> 
                                    @can('delete', $category)
                                        <li> 
                                            <form action="{{ route('admin.categories.destroy',[$category->slug]) }}" method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="span">
                                            
                                                        <i class="fas fa-trash"></i>  
                                                
                                                </button>
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