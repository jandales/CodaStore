@extends('layout.admin')

@section('content') 

<h1>Attributes</h1>

    <div class="flex justify-content-space-between m-t-2">
        <div class="forms-category">            
            <div class="form-title">
                <label for="email" class="title">Add new Attributes</label>                                 
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
                <button id="btnFormCreate" class="btn btn-primary w-3">SAVE</button>
                <button id="btnFormCancel" class="btn btn-danger w-3 ml10 hidden">CANCEL</button> 
            </div>
        </div>
        <div class="panel m-t-2 w-9"> 
            <div class="panel-body">
                <div class="toolbar justify-content-space-between action-toolbar hidden"> 
                    <label class="title selected-items">0 item Selected</label>
                    <div class="btn-action">                   
                        <span  id="btndelete" class="btn btn-light"><i class="fas fa-trash"></i></span>
                        <span  onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
                    </div>
                </div> 
                <div class="toolbar justify-content-space-between default-toolbar">                        
                    <label class="title">List of Attributes</label>
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
                    <tbody class="table-body">
                      
                                         
                    </tbody>      
                    </table> 
                    <br>             
                   
          
                                         
            
            </div>
        </div>
    </div>
    
    {{-- <div id="modal-add-variant" class="modal">
        <div class="modal-content add-variant">
            <div class="modal-title">
                <h4 class="h4-title">Add Variant</h4>
                <span class="modal-close"><i class="fas fa-times"></i></span>
            </div>
           <div class="form m-t-1 mb2">

            <div class="variants-wrapper">
                <div class="variants-list-wrapper">
                   
                </div>

                <input id="variant-name" placeholder="Please enter name enter and hit Enter" type="text" name="variant_name" value="">
                

            </div>   
               
            </div>
        </div>
    </div> --}}

    <script src="/js/admin/attributes.js"></script> 
   
    




@endsection