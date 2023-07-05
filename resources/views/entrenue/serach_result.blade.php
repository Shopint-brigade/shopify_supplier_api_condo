@extends('layouts.app')
@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-10">
            <h2 class='text-center text-white p-2 @if ($total > 0) bg-success @else bg-danger @endif'>
                <b>{{ $total }}</b> {{ __('results for') }} <b>{{ $title }}</b></h2>
            <h2 class="text-center mb-5">
                <a href="{{ route('admin.enterenue.search.form') }}" class=' @if($total > 0)text-success @else text-danger @endif'>
                    {{ __('Back to search') }}
                </a>
            </h2>
            @if ($total > 0)
                <table class="table">
                    <thead class="table-success">
                        <tr>
                            <th>&nbsp;</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('UPC') }}</th>
                            <th>{{ __('Available') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('MSRP') }}</th>
                            <th colspan='2' class='text-center'>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td class="align-middle">
                                    @isset($product['image'])
                                        <img width="100" src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                                    @endisset

                                </td>
                                <td class="align-middle">
                                    <p class="text-white bg-secondary p-2 mb-1">{{ $product['name'] }}</p>
                                    <span>{!! $product['description'] !!}</span>
                                </td>
                                <td class='align-middle'>{{ $product['upc'] }}</td>
                                <td class='align-middle'>{{ $product['quantity'] }}</td>
                                <td class='align-middle'>{{ $product['price'] }}</td>
                                <td class='align-middle'>{{ $product['msrp'] }}</td>
                                <td class='align-middle'>
                                    <button type="button" class="btn-close close-product" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </td>
                                <td class='align-middle'>
                                    @if (!in_array($product['upc'], $dbProductsUpcs))
                                        <a href="{{ route('admin.enterenue.pushProduct', $product['upc']) }}"
                                            target='_blank' class='text-success fw-bold'>{{ __('Push to Shopify') }}</a>
                                    @else
                                        <span
                                            class="fs-6 p-2 badge text-bg-success text-white">{{ __('Already pushed !') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>
@endsection
