function url(method, route){
    let form =  document.getElementById('form')
    form.setAttribute('action', route)
    form.setAttribute('method', method)
    form.submit() 
}

function published(route){
    let form =  document.getElementById('updateform')
    form.setAttribute('action', route)
    form.submit()
}

function urldelete(route, e){
    e.preventDefault();
    let form =  document.getElementById('deleteform')
    form.setAttribute('action', route)
    // form.submit()  
}

const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const _put = "PUT"
const _delete = "DELETE"

function requestDelete(url){
    let result = null
    $.ajax({
        url : url,
        type : 'POST',
        data : {
            _token : _token,
            _method : _delete,
        },
        async : false,
        success:function(response){
            result =  response
        }
    })
    return result;
}

function requestLists(url){
    let result = null
    $.ajax({
        url : url,
        type : 'GET',        
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}

function requestGet(url){
    let result = null
    $.ajax({
        url : url,
        type : 'GET',        
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}

function requestSearch(url, input){
    let result = null
    $.ajax({
        url : url,
        type : 'GET',  
        data : {
            search : input,
        },   
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}

function requestStore(url, data = {}){
    let result = null
    $.ajax({
        url : url,
        type : 'Post',
        data : data, 
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}