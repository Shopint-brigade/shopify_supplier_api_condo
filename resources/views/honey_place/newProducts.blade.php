@extends('layouts.app')
@section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="col-md-8">
        @if (count($newProducts) !== 0)
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">{{__('Shopify ID')}}</th>
                        <th scope="col">{{__('SKU')}}</th>
                        <!-- <th scope="col">{{__('Barcode')}}</th> -->
                        <th scope="col">{{__('Quantity')}}</th>
                        <th scope="col">{{__('Created at')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($newProducts as $product)
                    <tr>
                        <td>{{$product->intID}}</td>
                        <td>{{$product->sku}}</td>
                        <!-- <td>{{$product->barcode}}</td> -->
                        <td>{{$product->stock}}</td>
                        <td>{{$product->created_at->diffForHumans()}}</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
            @else
                <h2>{{__('there is no new product created by Shopify')}}</h2>
            @endif
        </div>
    </div>
</div>
@endsection