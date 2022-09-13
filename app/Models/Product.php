<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'productName',
        'amountAvailable',
        'cost',
        'sellerId'
    ];
    
    /**
     * Get the role record associated with the user.
     */
    public function seller()
    {
        return $this->belongsTo('App\Models\User', 'sellerId');
    }

    /**
     * Check if there is available quantity of the product
     */
    public function hasAvailableQuantity($quantity)
    {
        return $this->amountAvailable >= $quantity;
    }

    /**
     * Reduce the quantity of the product's stock
     */
    public function reduceQuantity($quantity)
    {
        if(!$this->hasAvailableQuantity($quantity)){
            return false;
        }
        
        $this->amountAvailable -= $quantity;
        $this->save();

        return true;
    }

}
