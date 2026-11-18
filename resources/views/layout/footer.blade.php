<div class="footer">
    <h5>&copy; {{ date('Y') }} Coda. All rights reserved. Designed and developed by Jesus Andales.</h5>
</div>
</div>{{-- .page-wrapper --}}
</div>{{-- .admin-main --}}
</div>{{-- #wrapper --}}

</div>
<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="{{ asset('js/main.js') }}"></script>
<script src="/library/ckeditor/ckeditor.js"></script>
@stack('scripts')

{{-- <script type="text/javascript">
    $(document).ready(function () {
        $('.ckeditor').ckeditor();
    });
</script> --}}


</body>
</html>
