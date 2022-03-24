

let properties = []
let hasVariants = false;

function populateProperties(name, value){
    let  exist = arrContains(properties, 'name' , name)
    if(exist){
        let index =  arrFindIndex(properties, 'name', name)
        properties[index].value = value                
        return
    }            
    properties.push({ name : name , value : value }) 
}


function getVariants(id){
    $.ajax({
        url : '/product/hasvariant/'+id,
        type : 'GET',
        data : {          
            id : id,         
        },    
        success : function(response){      
                hasVariants = response.hasvariant;
                response.variants.forEach(item => {
                    populateProperties(item,"");
                });            
        }
    })
}

function validateProperties()
{
    let result = []  
    let message = ""
    if(hasVariants){
       for (let i = 0; i < properties.length; i++) {
            if(properties[i].value == ""){
                message = "Please select " + properties[i].name
                result.push({status : true, message : message})
                return result; 
            }          
        }
    }
    result.push({status : false, message : message})
    return  result;
}