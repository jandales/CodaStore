@extends('layout.admin')

@section('content') 

<h1>Products</h1>




        <div class="tabs m-t-2">          
            <div class="form-header">
                <ul class="form-navigation">
                    <li><a href="{{ route('product.edit',[$product]) }}" >Item Information</a></li>
                    <li><a href="{{ route('product.variants',[$product]) }}"  class="active">Variants</a></li>
                    <li><a href="{{ route('stock.edit',[$product->stock])}}">Quantity</a></li>
                    <li><a href="{{ route('price.edit',[$product])}}">Pricing</a></li>
                    <li><a href="{{ route('product.download',[$product]) }}">Image Upload</a></li>  
                </ul>
            </div>

       
            <div class="form-content">           
                
                @if(session('success'))
                     <div class="alert alert-success">{{ session('success') }}</div>                
                @endif 

                <div class="tabs"  background="bg-default">                  

                    <div class="tab-header">
                        <ul class="tab-nav" background="bg-default">
                            <li><a href="#"  class="tabs-button" data-for-tab="1" >Attributes</a></li>
                            <li><a href="#"  class="tabs-button" data-for-tab="2" >Add</a></li>
                        </ul>
                    </div>
             

                <div class="tabs-content" data-tab= "1">

                    <table class="table table-attributes bg-white">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Values</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($product->variants as $variant)
                          
                            <tr>
                            
                                <td>{{ $variant->varaints->name }}</td>
                                <td>
                                    @foreach ($product->getAttr($product->id, $variant->variant_id) as $attribute)                                         
                                        <form  action="{{ route('product.attribute.destroy',[$attribute]) }}" class ="attribute-delete-form attribute-form"  method="POST">
                                            @csrf
                                            @method('delete')   
                                            <a  class="attribute-value mr-2"><span class="attribute-delete"><i class="fa fa-times"></i></span>  {{ $attribute->attributes->value }}</a>                          
                                        </form>                                                                       
                                    @endforeach                                    
                                </td>
                                <td>
                                    <form action="{{ route('product.variant.destroy',[$variant]) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <button  class="btn btn-danger btn-delete">Delete</button>
                                    </form>                                  
                                
                                </td>
                            </tr>
                            @endforeach

                            
                            
                        </tbody>
                    </table>

                </div>

                <div class="tabs-content" data-tab= "2">
                    <div class="bg-white p-2">
                        <div class="form-inline">
                            <select  id="variant" class="w-2 mr-2">
                                <option value="0">Select Attributes</option>
                              @foreach (variants() as $variant)
                                  <option value="{{ $variant->id }}">{{ $variant->name }}</option>
                              @endforeach
                            </select>
                            <button onclick="creatDomVariants()">add</button>
                        </div> 
    
                                 
                            <table class="table table-attributes bg-white">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Values</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="tbody">
                                   
                       
                                </tbody>
                            </table>
                            <form id="form" action="{{ route('product.attributes',[$product]) }}" method="post"> 
                                @csrf 
                            </form>
                    </div>
                 
                    
                </div>

            </div>
              
        

                    <div class="form-button m-t-2">
                        <button id="submit" class="btn btn-primary mr-2">Save</button>
                        <button class="btn btn-danger">Cancel</button>
                
                    </div>
                 </form>

                
        
            </div>       
        </div>


        <script>

            const variants = [];
            const attributes = [];

            // const formdelete = document.getElementById('deleteform');
            // const btndelete = document.querySelectorAll('.btn-delete')

            // btndelete.forEach(button => {
            //     button.addEventListener('click', function() {
            //         console.log(button.getAttribute('data'))
            //     })
            // })

            const attributeDelete = document.querySelectorAll('.attribute-delete');
            
            attributeDelete.forEach(attrDelete => {
                attrDelete.addEventListener('click', function(){
                    let form = attrDelete.closest('form');
                    event.preventDefault()
                    
                    form.submit();
                })
            })

           

            function  createbutton( name, text, id, className ) {

                let button = document.createElement( 'button' )
                button.id = id
                button.name = name
                button.innerHTML = text
                button.className = className

                return button;
            }

            function queryAttributes( id ){
                
                if ( document.getElementById( 'options-'+id ) )  return  

                let select = document.createElement( 'select' )
                select.id = 'options-'+id
                select.className ='w-2 mr-2 ' + 'row' + id  

                $.ajax({                
                    url: '/admin/products/attributes/'+ id ,
                    type: 'get',
                    data:{
                        id : id,
                    },
                    datatype:"json",          
                    success: function( data ) { 
                                                
                            data.forEach( attr => {                  
                
                            if( !checkIfQueryIsSelected( attr.id ) ) {

                                    let option = document.createElement( 'option' )
                                    option.value = attr.id;
                                    option.innerText = attr.value
                                    select.appendChild( option )

                            } 
                    });
                                    
                    }         
                });
            
                return select

            }

            function checkIfQueryIsSelected( attribute ) {

                if ( attributes.length == 0 ) return false

                for( i = 0; i < attributes.length; i++ ) {

                    if( attributes[i].attribute == attribute ) return true 

                }

            }
                
            function openSelectionAttributes( id, row ) {
            
                const a = document.querySelector( '.' + row )
                
                const main =  a.closest( '.attributes-container' ) 

                let attributeselect = document.createElement( 'div' )
                attributeselect.className = 'attribute-select m-t-2'

                let select = queryAttributes( id )
            
                let button1 = createbutton( 'add', 'Add' , 'btnAdd', 'btn btn-primary mr-2' ) 
                button1.setAttribute( 'onclick','addAtrribute( '+ id +')' )

                let button2 = createbutton( 'selectall', 'Select all' , 'btnselectall', 'btn btn-primary mr-2' )
                button2.setAttribute( 'onclick','selectallAttributes( '+ id +')' )   

                let button3 = createbutton('removeAll', 'Remove All' , 'btnremoveall', 'btn btn-danger mr-2' )
                button3.setAttribute( 'onclick','removeAllAttributes( '+ id +')' )   
                
                attributeselect.appendChild( select );
                attributeselect.appendChild( button1 )
                attributeselect.appendChild( button2 )
                attributeselect.appendChild( button3 )  

                main.appendChild( attributeselect )

                
            }

            function addAtrribute(id){

                const select = document.getElementById( 'options-'+id )
                const a = document.querySelector(  '.row' + id)            
                const content =  a.closest( '.attributes-content' )          
                
                let index = 0; 
                let text = ""
                
                Array.from(select.options).forEach(option => {             
                    if(select.value == option.value){
                        index =  option.value
                        text = option.text
                    } 
                })

                if(index == "") return; 
                
                select.remove(select.selectedIndex)           
                createAttributes(index,text,id)
                attributes.push({variant:id, attribute:index, name:text}) 
                removeAttributeSelect(id)
            
            }

            function removeAttributeSelect(row){
            
                const select = document.getElementById('options-'+row)
                const container = select.parentElement;
                const parentContainer = container.closest( '.attributes-container' )            

                if ( select.length == 0 ) {
                    parentContainer.removeChild( container )
                }
            }


            function resetSelectElement( id ){

                const select = document.getElementById( 'options-'+id )

                Array.from( select.options ).forEach( ( option, i ) => { 

                    option.remove( i )
                    
            }) 

            removeAttributeSelect( id )
            }

            function  removeAttributes( id, row ) {  
            
                const btn = document.querySelector( '.row' + row )  
                const attributesContent = btn.closest('.attributes-content') 
                const elem = document.getElementById( id )          

                for ( i = 0; i < attributes.length; i++ ) {
                
                    if( attributes[i].variant == row && attributes[i].attribute == id ) {
                        attributes.splice( i, 1 )              
                        attributesContent.removeChild( elem )  
                    } 

                }

            

            }

            function removeAllAttributes( id ) {
            
                const btn = document.querySelector( '.row' + id )  
                const attributesContent = btn.closest( '.attributes-content' )           
            
                
                for ( i = 0; i < attributes.length; i++ ) {
                    
                    if ( attributes[i].variant == id ) { 

                        const elem = document.getElementById( attributes[i].attribute )           
                        attributesContent.removeChild( elem )
                        attributes.splice( i, 1 )  
                        i--  

                    } 

                }

                resetSelectElement( id )             
            }

            function selectallAttributes( id ){

                const select = document.getElementById( 'options-'+id )

                let exist = 0

                for( i = 0; i < select.length; i++ ) {

                    for( j = 0; j < attributes.length; j++ ){
                        if( attributes[ j ].attribute == select.options[ i ].value ) {                         
                            exist = select.options[ i ].value
                        }
                    }

                    let value =  select.options[ i ].value
                    let text = select.options[ i ].text

                    if( select.options[ i ].value != exist ) {
                        createAttributes( value, text, id ) 
                        attributes.push( { variant:id, attribute:value, name:text } )     
                    }
                } 

                resetSelectElement( id )
            }

            function createAttributes( id, text, row ) {

                let a = document.createElement( 'a' )
                a.id = id           
                a.className = 'attribute-value mr-2'
                a.innerHTML = '<span onclick="removeAttributes( '+ id +', '+ row +' )"><i class="fa fa-times"></i></span>' +  text

                const btn = document.querySelector( '.row' + row )  
                const attributesContent = btn.closest( '.attributes-content' )     
                attributesContent.appendChild( a );

            }


            function arrExist( arr, value ){

                for ( let i = 0; i < arr.length; i++ ) {

                        if ( arr[i] == value ) return true

                } 

                return false
            }

            function creatDomVariants() {


                const elem = document.getElementById( 'variant' )
                
                let valueName = elem[ elem.selectedIndex ].text
         
                let value = elem.value 
                
                let className =  "row" + value

                if ( value == 0 ) return  alert( 'select attributes first' )            

                if ( arrExist( variants, value ) ) return  alert( 'attributes already exist' )
                
                let tr = document.createElement('tr')
                tr.className = 'tr'+value

                let td = document.createElement('td')
                td.innerHTML = '<p class="attribute-name">'+ valueName +'</p>'

                let td1 = document.createElement('td')

                let btnDelete = createbutton('btndelete', 'Delete', 'btndelete', 'btn btn-danger mr-2')        

                let tdButton = document.createElement('td')
                tdButton.className = 'vertical-center'
                tdButton.setAttribute('onclick', 'deleteVariants('+ value +')')
                tdButton.appendChild( btnDelete )

                let a = document.createElement('a')
                a.setAttribute('onclick', 'openSelectionAttributes('+ value +', "'+ className +'")'); 
                a.className = "attribute-add mr-2 " + className 
                a.innerHTML = '<i class="fa fa-plus"></i>'

                let mainContainer = document.createElement('div')
                mainContainer.className = 'attributes-container'

                let attributesContent = document.createElement('div')        
                attributesContent.className = 'attributes-content'            

                tr.appendChild(td)
                tr.appendChild(td1)
                tr.appendChild(tdButton)          

                td1.appendChild(mainContainer)
                mainContainer.appendChild(attributesContent)
                attributesContent.appendChild(a);         
            
                document.querySelector('.tbody').appendChild(tr)
            
                variants.push(value)
        
        
            }

            function deleteVariants( row ) {

                let index = getIndex( variants, row )
                variants.splice( index, 1 );                  

                for ( i = 0; i < attributes.length; i++ ) {
                        
                    if ( attributes[i].variant == row ) { 
                        attributes.splice( i, 1 )  
                        i--  
                    } 
                }

                const tr = document.querySelector( '.tr'+row )
                tr.parentElement.removeChild( tr )
            
            }

            function  getIndex( arr, value ) {
                for ( i = 0; i < arr.length; i++ ) {                
                    if ( arr[ i ]== value ) return i
                }
            }

            const submit = document.getElementById('submit')


            submit.addEventListener( 'click', function(){

                event.preventDefault()               

                const form = document.getElementById('form')
                let url = form.getAttribute('action');            
               
                let token =  $('input[name="_token"]').val();
            
                let id = 11
                $.ajax({                
                    url: url,
                    type: 'post',
                    data:{
                        _token : token,
                        attributes : attributes,
                        variants : variants,
                    },
                    datatype:"json",          
                    success: function( data ) {                 
                        window.location.href = data;
                    }         
                });

            })

        </script>

@endsection