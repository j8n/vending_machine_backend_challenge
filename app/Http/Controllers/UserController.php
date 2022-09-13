<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Role;
use App\Models\Product;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // check authorization
        if ($request->user()->cannot('viewAny', User::class)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // Get users
        $users = User::all();

        // return response
        return response()->json([
            'success' => true,
            'resources' => UserResource::collection($users)
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
        // validate
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:App\Models\User,username',
            'password' => 'required|min:8',
            'role_id' => 'required'
        ]);

        // check if fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ]);
        }

        // check role
        if(!in_array($request->input('role_id'), Role::getAvailableRoles())){
            return response()->json([
                'success' => false,
                'error' => 'Role provided is not correct',
            ]);
        }

        // create the new user
        $user = new User();
        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->deposit = 0;
        $user->role_id = $request->input('role_id');
        $user->save();

        // create a new token for the user registered
        $token = $user->createToken('login_token');

        // return response
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
            'access_token' => $token->plainTextToken
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // get user
        $user = User::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('view', $user)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // return response
        return response()->json([
            'success' => true,
            'resource' => new UserResource($user)
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
        // Get the user
        $user = User::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('update', $user)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:App\Models\User,username',
            'password' => 'nullable|min:8',
            'role_id' => 'nullable',
        ]);

        // check if fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ]);
        }

        // check role
        if(!in_array($request->input('role_id'), Role::getAvailableRoles())){
            return response()->json([
                'success' => false,
                'error' => 'Role provided is not correct',
            ]);
        }

        // Update
        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->role_id = $request->input('role_id');
        $user->save();

        // return response
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Get user
        $user = User::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('delete', $user)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // Delete
        $user->delete();

        // return response
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Deposit funds
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deposit(Request $request, $id)
    {
        // Get user
        $user = User::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('deposit', $user)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer'
        ]);

        // check if fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ]);
        }

        // Check if amount is valid
        if(!User::amountIsValid($request->input('amount'))){
            // return error response
            return response()->json([
                'success' => false,
                'error' => 'The amount is not valid. Valid amounts are: 5, 10, 20, 50, 100'
            ]);
        }

        // Add amount to user's deposit amount
        $user->depositAmount($request->input('amount'));

        // return response
        return response()->json([
            'success' => true,
            'newAmount' => $user->deposit,
        ]);

    }

    /**
     * Buy
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function buy(Request $request, $id)
    {
        // Get user
        $user = User::findOrFail($id);

        // Get product
        $product = Product::findOrFail($request->input('productId'));

        // check authorization
        if ($request->user()->cannot('buyAProduct', $user)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'amountOfProducts' => 'required|integer'
        ]);

        // check if fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ]);
        }

        // check if there is enough quantity of this product
        if(!$product->hasAvailableQuantity($request->input('amountOfProducts'))){
            return response()->json([
                'success' => false,
                'error' => 'There is not enough amount of this product. Current amount: ' . $product->amountAvailable ,
            ]);
        }

        // reduce the amount of the availability
        $product->reduceQuantity($request->input('amountOfProducts'));

        // calculate the total amount
        $totalAmount = $request->input('amountOfProducts') * $product->cost;

        // check if the user has the amount needed
        if(!$user->hasAmount($totalAmount)){
            return response()->json([
                'success' => false,
                'error' => 'Not enough amount in your deposit. Amount needed: ' . $totalAmount,
            ]);
        }

        // Remove totalAmount from the user's deposit amount
        $user->removeAmount($totalAmount);

        // calculate the change
        $change = $user->calculateChange();

        // return response
        return response()->json([
            'success' => true,
            'totalSpent' => $totalAmount,
            'newAmountAvailable' => $user->deposit,
            'productBought' => [
                'id' => $product->id,
                'name' => $product->productName
            ],
            'change' => $change
        ]);

    }

    /**
     * reset deposit to 0
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request, $id)
    {
        // Get user
        $user = User::findOrFail($id);

        // check authorization
        if ($request->user()->cannot('update', $user)) {
            // return response
            return response()->json([
                'success' => false,
                'error' => 'Forbidden'
            ]);
        }

        // Add amount to user's deposit amount
        $user->deposit = 0;
        $user->save();

        // return response
        return response()->json([
            'success' => true
        ]);
    }

}
