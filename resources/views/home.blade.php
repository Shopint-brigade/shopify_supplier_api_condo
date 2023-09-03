@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-black text-white fs-2">
                {{ __('Dashboard') }}
                </div>

                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif
                    <h2 class="bg-secondary text-white p-2 mb-0">{{ __('Honey')}}</h2>
                    <ul class="list-group">
                        {{-- <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.honey.products') }}">{{ __('Shopify products(all honey products on shopify store)') }}</a></li> --}}
                        <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.logs') }}">{{ __('Logs') }}</a></li>
                        <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.new.products') }}">{{ __('New Products(Created from shopify store)') }}</a></li>
                        <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.sync.images') }}">{{ __('Sync Product Images') }}</a></li>
                        <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.list.syned.products') }}">{{ __('Last 10 Synced products(Images)') }}</a></li>
                    </ul>
                    @include('entrenue.links')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection