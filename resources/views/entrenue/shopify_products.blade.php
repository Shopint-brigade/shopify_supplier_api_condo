@extends('layouts.app')
@section('content')
    <div class="row justify-content-center mt-4">
        <div class="col-10">
            <h1 class="text-center mb-5">{{ __('Products from Shopify') }}</h1>
            @if (count($products) !== 0)
                <table class="table">
                    <thead>
                        <tr class="table-success">
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('SKU(UPC)') }}</th>
                            <th scope="col">{{ __('Price') }}</th>
                            <th scope="col">{{ __('Quqntity') }}</th>
                            <th scope="col">{{ __('Update Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <th>{{ $product->title }}</th>
                                <td>{{ $product->upc }}</td>
                                <td>{{ $product->price }}</td>
                                <td>{{ $product->qty }}</td>
                                <td>{{ $product->updated_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                {{$products->links()}}
            @else
                <h2 class="text-center bg-secondary text-white">{{ __('No products !') }}</h2>
            @endif
        </div>
    </div>
@endsection
