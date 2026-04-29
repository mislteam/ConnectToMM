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
    <div class="alert alert-success alert-dismissible fade show alert-fixed" role="alert" data-auto-dismiss="5000">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show alert-fixed" role="alert" data-auto-dismiss="5000">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-auto-dismiss]').forEach(function(alertEl) {
            var timeout = parseInt(alertEl.getAttribute('data-auto-dismiss'), 10) || 5000;

            window.setTimeout(function() {
                if (!alertEl.isConnected) {
                    return;
                }

                if (window.bootstrap && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance(alertEl).close();
                    return;
                }

                alertEl.remove();
            }, timeout);
        });
    });
</script>

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
