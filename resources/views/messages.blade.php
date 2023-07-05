@if (session('success'))
    <div class="alert alert-success text-center" role="alert">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger text-center" role="alert">
        {{ session('error') }}
    </div>
@endif
@if (session('info'))
    <div class="alert alert-info text-center" role="alert">
        {{ session('error') }}
    </div>
@endif