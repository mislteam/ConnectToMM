<style>
    #swal2-html-container {
        font-size: 14px !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session('error'))
    <script>
        Swal.fire({
            title: 'Import Failed',
            text: '{{ session('error') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0049ad',
        });
    </script>
@endif

@if (session('success'))
    <script>
        Swal.fire({
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0049ad',
        });
    </script>
@endif
