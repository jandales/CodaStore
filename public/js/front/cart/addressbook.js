
let addressbook1 = [];

const tableBody = document.getElementById('table-body')
const btnaddress =  document.getElementById('addressbook')

btnaddress.addEventListener('click', function (){

    $.ajax({
        url : '/user/addressbook',
        type : 'Get',
        success : function(response){        
            createRow(response);
        }
    });


    openModal('modal-addressbook');
});



function createRow(addressbook) {
    tableBody.innerHTML = "";

    addressbook.forEach(address => {   

        let row = `<tr>
                <td class="text-align-center"><span><i class="${ addressIcon(address.type) }"></i></span></td>
                <td>${address.reciept_name}</td>
                <td>${address.street + " " + address.barangay + " " + address.city_municipality + " " + address.province}</td>
                <td>${address.reciept_number}</td>
                <td>${address.reciept_email ?? '' } </td>
                <td><input type="checkbox" onclick="checkBoxChange(this)" class="address-checkbox" name="selected" address = "${address.id }"  ${ defaultAddress(address.status) }></td>
                </tr>`   
        tableBody.innerHTML += row;       
        
    })   

    
}

function addressIcon(type){
    if(type == 0) return "fa fa-home"  
    return "fa fa-store"
}

function defaultAddress(address) {     
    if(address == 1)  return "checked"
    return "";      
}

function checkBoxChange()
{
    uncheckAll();
    event.currentTarget.checked = true; 
}

function uncheckAll()
{
    const addressCheckbox = document.querySelectorAll('.address-checkbox');
    addressCheckbox.forEach(checkbox => {  checkbox.checked = false  })
}

function submit(){

    const addressCheckbox = document.querySelectorAll('.address-checkbox');
    const token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let  address = 0;
    addressCheckbox.forEach(checkbox => { if(checkbox.checked) address =  checkbox.getAttribute('address') })

    $.ajax({                
        url : '/addressbook/set/' + address,
        method : "POST",
        data : {            
            _token : token,
            _method : 'put',
        },
        success:function(response){
            if(response.status == "success") return location.reload();       
        }
    });
}



