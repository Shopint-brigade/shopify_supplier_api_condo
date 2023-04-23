@extends('layouts.app')
@section('content')


<div class="row justify-content-center">
    <div class="col-md-8">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <h2>Sync product images</h2>

        <form action="{{ route('admin.syn.images.post')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="ptoductUrl" class="form-label">Honey product URL</label>
                <input name="product_url" type="text" class="form-control" id="ptoductUrl" placeholder="https://www.honeysplace.com/product/xxx/the-title">
            </div>
            <div class="mb-3">
                <label for="ptoductID" class="form-label">Shopiyfy products <b>({{count($products)}})</b></label>
                <select name="product_id" class="form-select" aria-label="Default select example">
                    <option disabled selected>Select Product</option>
                    @foreach ($products as $p )
                    <option value="{{$p->intID}}">{{$p->intID}} - {{$p->title}}</option>
                    @endforeach

                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>

@endsection