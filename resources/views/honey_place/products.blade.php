@extends('layouts.app')
@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>
            {{__('Products on shopfy store before sync')}}
            <span class="badge text-bg-success rounded-pill">{{$total}}</span>
        </h2>
        <hr>
        @if (count($products) !== 0)
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">{{__('Title')}}</th>
                    <th scope="col">{{__('Shopify ID')}}</th>
                    <th scope="col">{{__('SKU')}}</th>
                    <!-- <th scope="col">{{__('Barcode')}}</th> -->
                    <th scope="col">{{__('Quantity')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td>{{$product->title}}</td>
                    <td>{{$product->intID}}</td>
                    <td>{{$product->sku}}</td>
                    <!-- <td>{{$product->barcode}}</td> -->
                    <td>{{$product->stock}}</td>
                </tr>
                @endforeach

            </tbody>
        </table>
        {{$products->links()}}
        @else
        <h2>{{__('there is no product synced with shopify')}}</h2>
        @endif

    </div>
</div>

@endsection