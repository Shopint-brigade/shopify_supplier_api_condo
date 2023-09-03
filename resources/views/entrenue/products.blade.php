@extends('layouts.app')
@section('content')
    <div class="row justify-content-center mt-4">
        <div class="col-8">
            <h1 class="text-center mb-5">{{ __('Pushed products') }}</h1>
               <h2 class="text-center mb-5">
                <a href="{{ route('admin.enterenue.search.form') }}" class='text-success'>{{ __('Back to search') }}</a>
            </h2>
            @if (count($products) !== 0)
                <table class="table">
                    <thead>
                        <tr class="table-success">
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('UPC') }}</th>
                            <th scope="col">{{ __('Price') }}</th>
                            <th scope="col">{{ __('Quqntity') }}</th>
                            <th scope="col">{{ __('Push Date') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <th>{{ $product->title }}</th>
                                <td>{{ $product->upc }}</td>
                                <td>{{ $product->price }}</td>
                                <td>{{ $product->qty }}</td>
                                <td>{{ $product->created_at->diffForHumans() }}</td>
                                <td>
                                    <form method="post" action="{{ route('admin.enterenue.products.destory', $product) }}">
                                        @method('delete')
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                {{$products->links()}}
            @else
                <h2 class="text-center bg-secondary text-white">{{ __('Need to push products !') }}</h2>
            @endif
        </div>
    </div>
@endsection
