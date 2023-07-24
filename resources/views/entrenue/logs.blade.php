@extends('layouts.app')
@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>{{__('Check logs: Last time product quantity and price synced')}}</h2>
            <hr>
            @if($productSynced)
            <p class="fw-bold fs-5">{{__('Products synced at: ')}}<b class="text-decoration-underline">{{ $lastUpdated}}</b> <b><i class="text-success">({{ $lastUpdated->diffForHumans() }})</i></b></p>
            @else
                <h2>{{__('Need to sync your products quantity and price')}}</h2>
            @endif
          
        </div>
    </div>

@endsection