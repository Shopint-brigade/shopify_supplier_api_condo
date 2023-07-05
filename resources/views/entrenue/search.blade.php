@extends('layouts.app')
@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-6">
            @error('term')
                <div class="alert alert-danger mt-3 text-center">{{ $message }}</div>
            @enderror
            <h2 class="text-center">{{ __('Search') }}</h2>
            <form action="{{ route('admin.enterenue.search') }}" method='POST'>
                @csrf
                <div class="fw-semibold text-secondary">{{__('Write search term and press Enter')}}</div>

                <input type="text" name="term" class="form-control" placeholder="{{ __('Search for product...') }}">

            </form>
        </div>
    </div>
@endsection
