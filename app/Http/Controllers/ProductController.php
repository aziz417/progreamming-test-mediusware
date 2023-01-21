<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $title = $request->title;
        $variant_ids = $request->variants ? array_map('intval', $request->variants) : [];
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;

        $variants = Variant::get();

        $variants_price_keys = [$price_from, $price_to, $variant_ids];

        $products = Product::query()
            ->with('variants', 'prices')
            ->with('prices.productVariantOne')
            ->with('prices.productVariantTwo')
            ->with('prices.productVariantThree')
            ->when($title, function ($query, $title) {
                return $query->where('title', 'like', '%' . $title . '%');
            })
            ->when($date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->whereHas('prices', function ($q) use ($variants_price_keys) {

                $price_from = $variants_price_keys[0];
                $price_to = $variants_price_keys[1];
                $vIds = $variants_price_keys[2];

                $q->when($price_from, function ($query, $price_from) {
                    return $query->where('price', '>=', intval($price_from));
                })->when($price_to, function ($query, $price_to) {
                    return $query->where('price', '<=', intval($price_to));
                })->when($vIds, function ($query, $vIds) {
                    return $query->whereIn('product_variant_one', $vIds)
                        ->orWhereIn('product_variant_two', $vIds)
                        ->orWhereIn('product_variant_three', $vIds);
                });
            })
            ->paginate(3);
        $products->appends($request->all());

        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
//        dd($variants);
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $product = Product::create(['title' => $request->product_name, 'sku' => $request->product_sku, 'description' => $request->product_description]);

//        $product_image = new ProductImage();
//        if ($request->hasFile('product_image')) {
//            foreach ($request->file('product_image') as $img) {
//                $file = $img;
//                $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
//                $file->move(public_path('uploads/products'), $filename);
//                // save filename to database
//                $product_image->create(['product_id' => $product->id, 'file_path' => $filename]);
//            }
//        }

//        $product_variant = new ProductVariant();
//        foreach ($request->product_variant as $p_variant) {
//            foreach ($p_variant['value'] as $variant_id) {
//                $variant_id_array = explode('|', $variant_id);
//                $product_variant->create(
//                    [
//                        'variant' => $variant_id_array[1],
//                        'variant_id' => (int) $variant_id_array[0],
//                        'product_id' => $product->id
//                    ]);
//            }
//        }

        $column_name = [1 => 'one', 2 => 'two', 3 => 'three'];
        foreach ($request->product_preview as $price) {
            $pv_prices = new ProductVariantPrice();
            $attrs = explode("/", $price['variant']);

            $product_variant_ids = [];
            for ($i = 0; $i < count($attrs) - 1; $i++) {
                $product_variant_ids[] = ProductVariant::select('id')->where('variant', $attrs[$i])->latest()->first()->id;
            }

            for ($i = 1; $i <= count($product_variant_ids); $i++) {
                $pv_prices->{'product_variant_' . $column_name[$i]} = $product_variant_ids[$i - 1];
            }

            $pv_prices->price = $price['price'];
            $pv_prices->stock = $price['stock'];
            $pv_prices->product_id = $product->id;
            $pv_prices->save();
        }
        return redirect()->back();
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        $product = Product::with(['prices','variants'])->find($product->id);
//        dd($product);
        return view('products.edit', compact('product','variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
