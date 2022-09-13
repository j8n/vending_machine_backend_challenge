<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Product;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test a create buyer user action.
     *
     * @return void
     */
    public function test_create_buyer_user()
    {
        // the new buyer user data;
        $buyerUserData = [
            'username' => 'john buyer',
            'password' => '123123123',
            'role_id' => Role::BUYER
        ];

        // create the user
        $response = $this->post(
            route('users.store'),
            $buyerUserData
        );

        $response->assertStatus(200);
    }

    /**
     * Test user deposit.
     *
     * @return void
     */
    public function test_user_deposit()
    {
        // create a test buyer user
        $user = User::create([
            'username' => 'john',
            'password' => '123123123',
            'role_id' => Role::BUYER
        ]);

        // deposit amount to test
        $depositAmount = 100;

        // set acting as
        $this->actingAs($user);

        // make the call
        $response = $this->post(
            route('users.deposit', $user->id),
            [
                'amount' => $depositAmount
            ]
        );

        $response->assertStatus(200);
    }

    /**
     * Test user buy.
     *
     * @return void
     */
    public function test_user_buy()
    {
        // amount to buy
        $amountToBuy = 2;

        // create a test seller user
        $sellerUser = User::create([
            'username' => 'john seller',
            'password' => '123123123',
            'role_id' => Role::SELLER
        ]);

        // create a test product
        $product = Product::create([
            'productName' => 'Product One',
            'amountAvailable' => 100,
            'cost' => 150,
            'sellerId' => $sellerUser->id
        ]);

        // create a test buyer user
        $buyerUser = User::create([
            'username' => 'john buyer',
            'password' => '123123123',
            'role_id' => Role::BUYER
        ]);

        // set acting as
        $this->actingAs($buyerUser);

        // make the call
        $response = $this->post(
            route('users.buy', $buyerUser->id),
            [
                'productId' => $product->id,
                'amountOfProducts' => $amountToBuy
            ]
        );

        $response->assertStatus(200);
    }

    /**
     * Test user change.
     *
     * @return void
     */
    public function test_user_change_calculation()
    {
        // create a user
        $user = User::create([
            'username' => 'john buyer',
            'password' => '123123123',
            'role_id' => Role::BUYER
        ]);

        // calculate change for 0 deposit
        $emptyChange = $user->calculateChange();

        // test
        $this->assertEquals($emptyChange[100], 0);
        $this->assertEquals($emptyChange[50], 0);
        $this->assertEquals($emptyChange[20], 0);
        $this->assertEquals($emptyChange[10], 0);

        // deposit amounts
        $user->depositAmount(100);
        $user->depositAmount(100);
        $user->depositAmount(20);
        $user->depositAmount(10);

        // calculate change
        $change = $user->calculateChange();

        // test
        $this->assertEquals($change[100], 2);
        $this->assertEquals($change[50], 0);
        $this->assertEquals($change[20], 1);
        $this->assertEquals($change[10], 1);
    }

}
