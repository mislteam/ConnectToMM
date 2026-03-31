<style>
    .alert-fixed {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;
    }
</style>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show alert-fixed" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show alert-fixed" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show alert-fixed" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif -->
