@extends('layout.admin')

@section('content') 

<h1>Categories</h1>

    <div class="flex justify-content-space-between m-t-2">
        <div class="forms-category">
            
            <div class="form-title">
                <label for="email" class="title">Add new Category</label>                                 
            </div>           
            <div class="form-block">
                <label for="email">Name</label>
                <input type="text" name="name" value="">                 
            </div>
            <div class="alert-m  alert-danger">
                <div class="flex justify-content-space-between">
                    <label class="message">This is an alert box.</label>
                    <span class="closebtn " onclick="alertClose(this)"><i class="fas fa-times"></i></span>
                </div> 
              </div>
            <div class="form-block">
                <label for="email">Descriptions</label>
                <input type="text" name="description" value="">                 
            </div>
            <div class="form-block">
            <label for="email">Slug</label>        
                <input type="text" name="slug" value="">                 
            </div>
            <div class="flex">
                <button id="btncategoryCreate" class="btn btn-primary w-3">SAVE</button>
                <button id="btncategoryCancel" class="btn btn-danger w-3 ml10 hidden">CANCEL</button> 
            </div>
        </div>




        <div class="panel-table w-9">              
           
                <div class="toolbar justify-content-space-between action-toolbar hidden"> 
                    <label class="title selected-items">0 item Selected</label>
                    <div class="btn-action">                   
                        <span  onclick="deleteCategories()" class="btn btn-light"><i class="fas fa-trash"></i></span>
                        <span  onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
                    </div>
                </div> 
                <div class="toolbar default-toolbar">                        
                            <div class="search-input"> 
                                <span class="icon-right" onclick="search()"><i class="fas fa-search"></i></span>                                                        
                                <input  class="txtsearch" type="text" placeholder="Search"  name="search" value="">
                                <span class="icon-left close-search" onclick="closeSearch()"><i class="fas fa-times search-close-icon hidden"></i></span> 
                            </div>  
                </div>
                <form id="form" method="post" action="">  
                    @csrf
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
                        <tbody class="categories-body">                        
                                            
                        </tbody>      
                    </table> 
                </form>
               
          
        </div>
    </div>
  


    <script src="/js/admin/categories.js"></script>

@endsection