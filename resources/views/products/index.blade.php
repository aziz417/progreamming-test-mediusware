@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route('product.index') }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variants[]" id="" class="js-example-basic-multiple form-control" multiple="multiple">
                        @forelse($variants as $variant)
                            <optgroup label="{{ $variant->title }}">
                                @forelse($variant->product_variants as $product_variant)
                                    <option value="{{ $product_variant->id }}">{{ $product_variant->variant }}</option>
                                @empty
                                    <p>No Product Variants</p>
                                @endforelse
                            </optgroup>
                        @empty
                            <p>No Variants</p>
                        @endforelse
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                               class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $s = 1;
                    @endphp
                    @foreach ($products as $key => $product)
                        {{-- {{dd($product)}} --}}
                        <tr>
                            <td>{{$s++}}</td>
                            <td>{{$product->title}} <br>{{$product->created_at}}</td>
                            <td>{{ Str::limit($product->description,15)}}</td>

                            <td>
                                @php
                                    $chunk = 4;
                                @endphp
                                @foreach ($product->prices->chunk($chunk) as $key => $variations)
                                    @php
                                        $skipCount = ceil(count($product->prices) / $chunk);
                                        $skip = $skipCount ? count($product->prices) / $skipCount : $chunk;
                                    @endphp

                                    <div class="{{ $key != 0 ? 'variation' : '' }}"
                                         style="display: {{ $key != 0 ? 'none' : 'block' }}">
                                        @foreach ($variations->take($skip) as $variation)
                                            <dl class="row mb-0" id="variant">
                                                <dt class="col-sm-3 pb-0">
                                                    {{ optional($variation->productVariantOne)->variant }}
                                                    /{{ optional($variation->productVariantTwo)->variant }}{{ optional($variation->productVariantThree)->variant != null ? '/' . optional($variation->productVariantThree)->variant : '' }}
                                                </dt>
                                                <dd class="col-sm-9">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-4 pb-0">Price
                                                            : {{ number_format($variation->price, 2) }}</dt>
                                                        <dd class="col-sm-8 pb-0">InStock
                                                            : {{ number_format($variation->stock, 2) }}</dd>
                                                    </dl>
                                                </dd>
                                            </dl>
                                        @endforeach
                                    </div>
                                @endforeach
                                <button onclick="toggleVariation(this)" class="btn btn-sm btn-link">Show more</button>
                            </td>

                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    @php
                        $showing = $products->perPage() * ($products->currentPage()-1) + 1;
                        $to = $products->perPage() * $products->currentPage();
                        $total = $products->total();
                    @endphp
                    <p>
                        Showing {{$showing > $total ? $total : $showing}}
                        to
                        {{$to > $total ? $total : $to}}
                        out of
                        {{$total}}
                    </p>
                </div>
                <div>
                    {{ $products->links()}}
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

    <script>
        const toggleVariation = (obj) => {
            let btnText = $(obj).text();

            $(obj).closest('td').find('.variation').toggle();

            if (btnText === 'Show more') {
                $(obj).text('Hide');
            } else {
                $(obj).text('Show more');
            }
        }

        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();
        });
    </script>

@endsection
