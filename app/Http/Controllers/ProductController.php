<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get producs
        $products = Product::all();

        // return response
        return response()->json([
            'success' => true,
            'resources' => ProductResource::collection($products)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // check authorization
        if ($request->user()->cannot('create', Product::class)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'productName' => 'required|string',
            'amountAvailable' => 'required|integer',
            'cost' => 'required'
        ]);

        // check if fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ]);
        }

        // check if the cost is a multiple of 5
        if($request->input('cost') % 5 != 0){
            return response()->json([
                'success' => false,
                'error' => 'The cost must be in multiples of 5.',
            ]);
        }

        // create the new product
        $product = new Product();
        $product->productName = $request->input('productName');
        $product->amountAvailable = $request->input('amountAvailable');
        $product->cost = $request->input('cost');
        $product->sellerId = $request->user()->id;
        $product->save();

        // return response
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // get product
        $product = Product::findOrFail($id);

        // return response
        return response()->json([
            'success' => true,
            'resource' => new ProductResource($product)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // get the product
        $product = Product::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('update', $product)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'productName' => 'required|string',
            'amountAvailable' => 'required|integer',
            'cost' => 'required'
        ]);

        // check if fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ]);
        }

        // check if the cost is a multiple of 5
        if($request->input('cost') % 5 != 0){
            return response()->json([
                'success' => false,
                'error' => 'The cost must be in multiples of 5.',
            ]);
        }

        // update the new product
        $product->productName = $request->input('productName');
        $product->amountAvailable = $request->input('amountAvailable');
        $product->cost = $request->input('cost');
        $product->save();

        // return response
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Get product
        $product = Product::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('delete', $product)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // Delete
        $product->delete();

        // return response
        return response()->json([
            'success' => true
        ]);
    }
}
