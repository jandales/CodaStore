@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Users</h1>
   
</div>


<div class="row" >
    <div class="panel m-t-2 w-12">
        <div class="panel-header">
            
            

                <div class="toolbar">
                    <div class="btn-toolbar justify-content-between defualt-toolbar" >                   
                        
                        <form id="formSearch" action="{{ route('admin.users.search') }}" method="post">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="search"  placeholder="Search here" value="{{old('search')}}">
                                <div class="input-group-append">
                                <span onclick="document.getElementById('formSearch').submit()" class="input-group-text" ><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                        </form>
                  </div>  
    
                  <div class="btn-toolbar action-toolbar">                  
           
                        
                        <a href="javascript:{}" onclick="url('/admin/users/delete')" class="btn btn-danger mr-1" class="btn btn-secondary">delete</a>       
                      
        
                        <a href="javascript:{}" onclick="clearselection()"  class="btn btn-primary mr-1" class="btn btn-secondary">Clear Selection</a>
               
                </div> 
                
                </div>
    
                
            
           
             @if(session('success'))
              <br>
                  <div class="alert alert-success">{{ session('success') }}</div>    
             @endif
                 
   
          
        </div>
        <div class="panel-body">
           
            <table class="table">
                <thead>
                    <tr>
                        <th>                   
                             <div class="checkbox">

                                <input type="checkbox" id="parentCheckbox" name="checkbox" >
                             
                             </div>
                         </th>
                        <th>Name</th>
                        <th>Email</th>                                          
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody>
                    <form id="form"  method="post">
                        @csrf 
                        @method('delete')
                @if ( count($users) != 0 )

                @foreach ($users as $user)                   
                
                @if ($user->delete_at != 1)
                <tr>
                    <td>
                        <div class="table-action">
                           <div class="checkbox">
                   
                               
                                <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $user->id }}">
                         
                           </div>
                           
                            <a  href="#"  class="btn btn-danger" onclick="destroy('/admin/users/destroy/{{$user->id}}')">Delete</a>
                           
                            
                        </div>
                       
                    </td>
                    <td>{{ $user->name }}</td>                        
                    <td>{{$user->email}}</td> 
                    <td><p>{{ $user->contact }}</p></td>
                    <td><p>0</p></td> 
                    <td><p>{{ $user->created_at}}</p></td>

                </tr>
                @endif
              
                @endforeach
                @else
                <tr><td>No found Record</td></tr>
                @endif
  

                  
                  
                </form>
                </tbody>
            </table>
        
        
            <form id='destroy' action="{{ route('admin.users.destroy', [$user]) }}" method="post">
                @csrf      
                @method('put')  
            </form>
        
            
        </div>
    </div>

   
</div>


<script>   


const  parentCheckBox = document.getElementById('parentCheckbox')
const childCheckbox =  document.querySelectorAll('.childCheckbox')

parentCheckBox.addEventListener('change', function(){
  
    childCheckbox.forEach(elem => {
        elem.checked = this.checked;
        let tr  = elem.closest('tr')
        if(elem.checked){
            tr.style.background = "#f5f5f5"
           toolbar()
        }

        if(!elem.checked){
            tr.style.background = "none"
            toolbar(false)
        }
      
    })

})

childCheckbox.forEach(checkbox => {

    checkbox.addEventListener('change', function(){
        if(checkbox.checked){
           rowfucos(checkbox)
        }
        if(!checkbox.checked){
          rowfucos(checkbox, false)     
        }
        checked()      
    })  

 
})

function checked(){

  let childCount = childCheckbox.length
  let checkedfalse = 0
  let checkedtrue = 0

  childCheckbox.forEach(child => {
      if( child.checked ){

        checkedtrue += 1

        if(checkedtrue != 0){
            toolbar();
        }

      }
      else{
        checkedfalse += 1 
      }
  })

  if( checkedtrue == childCount ){
    parentCheckBox.checked = true
    toolbar()    
  }
  if( checkedfalse == childCount){
      parentCheckBox.checked = false
      toolbar(false)
  }


  
}

function toolbar(bool = true){

    const actiontoolbar = document.querySelector('.action-toolbar')
    const defaulttoolbar = document.querySelector('.defualt-toolbar')

    if(bool){
        display(actiontoolbar,"block") 
        
    }
    else{
        display(actiontoolbar,"none") 
        display(defaulttoolbar,"flex")
    }
}

function clearselection(){

    toolbar(false)
    parentCheckBox.checked = false
    childCheckbox.forEach(child => {
        child.checked = false;
        rowfucos(child,false)
    })
}


function rowfucos(elem,bool = true){

    let tr = elem.closest('tr') 

    if(bool){    
        tr.style.background  = "#f5f5f5" 
    }
    else{
        tr.style.background  = "none"
    }
}

function display(elem, value){
    elem.style.display = value
}

function url(route){
    let form =  document.getElementById('form')
    form.setAttribute('action', route)  
    form.submit() 
}

function destroy(route)
{
    let form =  document.getElementById('destroy')
    form.setAttribute('action', route)    
    form.submit() 
}













</script>


@endsection