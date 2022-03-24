
$(document).ready(function(){
   
    function removeAlert(){
  
      const alert = document.querySelectorAll('.alert-js');
      alert.forEach(elem => {
          elem.remove();
      }); 
    }  
    function clearallinput(){
      const alert = document.querySelectorAll('input');
      alert.forEach(elem => {
        elem.value = "";
    });
  
    } 
    function printMsg (msg) {
  
        removeAlert(); 
        
        var el = $(document).find('#modal-form'); 
  
        if(msg.error){       
          el.before($('<div style="margin-top:15px;" class="alert alert-danger alert-js">'+msg.error+'</div>'));
          el[0].reset();      
          return;        
        }
  
        if(msg.success){        
          el.before($('<div style="margin-top:15px;" class="alert alert-success alert-js">'+msg.success+'</div>')); 
          el[0].reset();
          return;
        }
        
    } 
    function  alerts(errors){
        $.each(errors, function (i, error) {
            var el = $(document).find('[name="'+i+'"]');                   
            el.after($('<div class="alert alert-danger alert-js m-t-10">'+error[0]+'</div>'));
          });
    }
    $('#btn-update-passowrd').click(function(){
 
        let form  =  document.querySelector('.form-password');
        let url = form.getAttribute('action');         
        let password = $('input[name="password"]').val();
        let password_confirmation = $('input[name="password_confirmation"]').val();
        let token =  $('input[name="_token"]').val();

        $.ajax({                
            url: url,
            type: 'post',
            data: {
                password : password,
                password_confirmation : password_confirmation,
                _token : token
            },          
            success: function(data) {                                
                 printMsg(data); 
                               
            },
            error: function (xhr) { 
              clearallinput();
              removeAlert();           
              if (xhr.status == 422) {
                  var errors = xhr.responseJSON.errors;             
                 alerts(errors);
            }
          }
        
          });
      
    });

    $('#upload-submit-avatar').click(function(){
        //stop submit the form, we will post it manually.
        event.preventDefault();
  
        let form =  $('#upload-avatar')[0];
        let url = form.getAttribute('action');   
        let data = new FormData(form); 
      
      $.ajax({                
          url: url,
          type: 'post',
          data: data,
          contentType: false,
          cache:false,
          processData:false,
          success: function(data) {                        
                window.location = "/admin/dashboard";             
          },
          error: function (xhr) { 
            clearallinput();
            removeAlert();           
            if (xhr.status == 422) {
                var errors = xhr.responseJSON.errors;             
                alerts(errors);
          }
        }
      
        });
    
  });

  $('#wish-lists-trigger').click(function(){

    alert('dadad');
      $.ajax({                
        url: '/wishlist',
        type: 'get',      
        success: function(data) {                        
            console.log(data);      
        }     
    
      });



  });



  
   
  
  
  
  
});


  
  
  