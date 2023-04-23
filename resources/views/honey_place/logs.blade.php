@extends('layouts.app')
@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>{{__('Check logs: Last time quantity synced')}}</h2>
            <hr>
            @if($productSynced)
            <p>{{__('Products quantity synced at: ')}}<b>{{ $lastUpdated }}</b></p>
            @else
                <h2>{{__('Need to sync your products quantity')}}</h2>
            @endif
          
        </div>
    </div>

@endsection