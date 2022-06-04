
///array function // get array index
export function arrFindIndex( arr,key,value ){    
    for (let i = 0; i < arr.length; i++ ) { 
        if(arguments.length === 2) if(arr[i] === key) return i
        if(arguments.length === 3) if ( arr[i][key] == value ) return i
    }
}

export function arrContains(arr, key, value){    
    for ( let i = 0; i < arr.length; i++ ) { 
       
        if(arguments.length === 2) if(arr[i] === key) return true        
        if(arguments.length === 3) if(arr[i][key] === value) return true
    }     
    return false
}

export function arrRemove(arr, key, value){   
    for( let i = 0; i < arr.length; i++ ){      

        if(arguments.length === 2) if(arr[i] == key) {         
            return  arr.splice(i, 1) 
        }
       
        if(arguments.length === 3) if(arr[i][key] == value) return arr.splice(i, 1)
    }
    return arr;
}