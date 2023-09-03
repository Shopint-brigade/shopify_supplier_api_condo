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
        <hr>
        <form action="{{ route('admin.syn.images.post')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="ptoductUrl" class="form-label">Honey product URL</label>
                <input name="product_url" type="text" class="form-control" id="ptoductUrl" placeholder="https://www.honeysplace.com/product/xxx/the-title">
            </div>
            <div class="mb-3">
                <label for="ptoductID" class="form-label">Shopiyfy products <b>({{count($products)}})</b></label>
                 <div class="form-text fw-semibold text-dark">{{__('You can search product is or name.')}}</div>
                <input name="search" type="text" id="product-search" class="form-control" data-url="{{ route('admin.product.search') }}">
                <input type="hidden" id="product_id" name="product_id">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>

@endsection
@push('script')
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
    $('#product-search').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: $('#product-search').data('url'),
                dataType: 'json',
                data: {
                    search: request.term
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.title,
                            value: item.intID
                        }
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $('#product_id').val(ui.item.value);
        }
    });
</script>
@endpush