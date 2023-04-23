@extends('layouts.app')
@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>{{__('Last 10 synced prpducts (images synced)')}}</h2>
        <br><hr>
        @if (count($products) !== 0)
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">{{__('Shopify ID')}}</th>
                    <th scope="col">{{__('SKU')}}</th>
                    <th scope="col">{{__('Barcode')}}</th>
                    <th scope="col">{{__('Quantity')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td>{{$product->intID}}</td>
                    <td>{{$product->sku}}</td>
                    <td>{{$product->barcode}}</td>
                    <td>{{$product->stock}}</td>
                </tr>
                @endforeach

            </tbody>
        </table>
        @else
        <h2>{{__('there is no product synced with shopify')}}</h2>
        @endif

    </div>
</div>

@endsection