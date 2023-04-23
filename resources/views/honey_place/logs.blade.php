@extends('layouts.app')
@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            @if($productSynced)
            <h2>{{__('Products quantity synced at: ')}}{{ $lastUpdated }}</h2>
            @else
                <h2>{{__('Need to sync your products quantity')}}</h2>
            @endif
          
        </div>
    </div>

@endsection