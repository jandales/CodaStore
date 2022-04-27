<div class="footer">
    <h5>All Rights Reserved by store admin. Designed and Developed by Jesus Andales.</h5>
</div>
</div>
</div>

</div>

<script src="/js/admin.js"></script>
<script src="/js/admin/modal.js"></script>
<script src="/js/front/tabs.js"></script>
<script src="/js/front/jquery.min.js"></script>
<script src="/js/fileuploader.js"></script>
<script src="/js/admin/arrayFunc.js"></script>
<script src="/js/admin/checkbox.js"></script>
<script src="/js/admin/request.js"></script>
{{-- <script type="text/javascript" src="froala-editor/js/froala_editor.pkgd.min.js"></script> --}}

{{-- <script> 
    var editor = new FroalaEditor('#short-description');
    var editor1 = new FroalaEditor('#long-description');
</script> --}}

<script>

    document.addEventListener('DOMContentLoaded', function(){
            deliver();
    })   
    function deliver(){
        const token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        $.ajax({
            url : '/admin/orders/deliver',
            method : 'POST',
            data : {
                _token : token,
                _method : 'PUT'
            },
            success : function(response) {              
            }
        })
    }
</script>
</body>
</html>
