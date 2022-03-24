

const token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
function post(route, data)
{      
    $.ajax({
        url : route,
        method : 'POST',
        data : data,
        success : function(response){           
            return response;
        }
    })
}

function put(route, data)
{
    $.ajax({
        url : route,
        method : 'POST',
        data : data,
        success : function(response){           
            return response;
        }
    }) 
}


