<script src="{{ asset('assets/js/sweetalert/sweetalert2@11.js') }}"></script>
<style>
    #swal2-html-container {
        font-size: 14px !important;
    }
</style>

@if (session('success') || request()->get('saved'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0049ad',
                allowOutsideClick: false,
                allowEscapeKey: true,
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error',
                text: @json(session('error')),
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0049ad',
            });
        });
    </script>
@endif

@if (session('error_popup_html'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error',
                html: @json(session('error_popup_html')),
                icon: 'error',
                showCloseButton: true,
                closeButtonAriaLabel: 'Close',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0049ad',
            });
        });
    </script>
@endif

@if ($errors->any() && !session('error_popup_html'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationErrors = @json($errors->all());
            const validationHtml = validationErrors
                .map(function(message) {
                    const item = document.createElement('div');
                    item.textContent = message;
                    return item.innerHTML;
                })
                .join('<br>');

            Swal.fire({
                title: 'Validation Error',
                html: validationHtml,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0049ad',
            });
        });
    </script>
@endif
