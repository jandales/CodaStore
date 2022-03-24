function quantity(action)
{ 
    switch(action)
    {
        case 'minus' :                        
            minus();
        break;

        case 'add' :
            add();
        break;
    }
}

function minus()
{
    const input = document.querySelector('.quantity-input');
    const input1 = document.querySelector('.quantity');
    
    let qty = parseInt(input.value);
    if(qty == 0) return; 
     qty -= 1;
    input.value =  qty;
    input1.value = qty;
            
}

function add()
{
    const input = document.querySelector('.quantity-input');
    const input1 = document.querySelector('.quantity');
    
    let qty = parseInt(input.value); 
    qty += 1;
    input.value =  qty;
    input1.value = qty;
}