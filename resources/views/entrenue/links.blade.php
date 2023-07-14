<h2 class="bg-success text-white p-2 mb-0">{{ __('Enterenue') }}</h2>
<ul class="list-group">
    <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.enterenue.logs') }}">{{ __('Logs') }}</a></li>
    <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.enterenue.search.form') }}">{{ __('Search for products in Enterenue') }}</a></li>
    <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.enterenue.synced.products') }}">
    {{ __('Shopify Pushed Products') }}
    <span class="badge bg-success rounded-pill fs-6 p-2">{{$enterenueDBProductsCount}}</span>
    </a></li>
    <li class="list-group-item"><a class="navbar-brand" href="{{ route('admin.enterenue.shopify.products') }}">
    {{ __('Products on shopify') }}
    <span class="badge bg-success rounded-pill fs-6 p-2">{{$enterenueShopifyProductsCount}}</span>
    <span class="form-text fw-semibold text-success mx-2">{{__('(All products from shopify will be aupdated continuously.)')}}</span>
    </a></li>
</ul>
