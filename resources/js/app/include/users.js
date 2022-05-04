
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

    $('#user-submit').click(function(){
          //stop submit the form, we will post it manually.
          event.preventDefault();

          var _token = $("input[name='_token']").val();
          var password = $("input[name='password']").val();
          var validator = $("input[name='validator']").val();
          var confirmPassword = $("input[name='confirmPassword']").val();

          

        $.ajax({                
            url: "/users/changepassword",
            type: 'post',
            data: {
                _token:_token,
                password : password,
                confirmPassword : confirmPassword,
                validator : validator
            },
            success: function(data) {                                
                 printMsg(data);
            },
            error: function (xhr) { 
              clearallinput();
              removeAlert();           
              if (xhr.status == 422) {
                  var errors = xhr.responseJSON.errors;             
                  $.each(errors, function (i, error) {
                    var el = $(document).find('[name="'+i+'"]');                   
                    el.after($('<div class="alert alert-text-danger alert-js">'+error[0]+'</div>'));
               });
            }
          }
        
          });
      
    });






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

    
    $('#upload-submit-avatar').click(function(){
      //stop submit the form, we will post it manually.
      event.preventDefault();

      let form =  $('#upload-avatar')[0];
      let data = new FormData(form);
   
      var _token = $("input[name='_token']").val();
      var avatar = $("input[name='avatar']").val();

    
    $.ajax({                
        url: "/upload/avatar",
        type: 'post',
        data: data,
        contentType: false,
        cache:false,
        processData:false,
        success: function(data) {                                
             printMsg(data);
             window.location = "/account";
        },
        error: function (xhr) { 
          clearallinput();
          removeAlert();           
          if (xhr.status == 422) {
              var errors = xhr.responseJSON.errors;             
              $.each(errors, function (i, error) {
                var el = $(document).find('[name="'+i+'"]');                   
                el.after($('<div class="alert alert-text-danger alert-js">'+error[0]+'</div>'));
           });
        }
      }
    
      });
  
});


  




});

