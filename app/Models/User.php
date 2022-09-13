<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The valid amounts a user can add to their account
     *
     * @var array<int, string>
     */
    protected static $validAmounts = [5, 10, 20, 50, 100];

    // valid amounts getter
    public static function getValidAmounts()
    {
        return self::$validAmounts;
    }

    /**
     * Check if amount is valid
     */
    public static function amountIsValid($amount)
    {
        if(in_array($amount, User::getValidAmounts())){
            return true;
        }

        return false;
    }

    /**
     * Get the role record associated with the user.
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * Check if the user is admin.
     */
    public function isAdmin()
    {
        return $this->role_id === Role::ADMIN;
    }

    /**
     * Check if has available amount
     */
    public function hasAmount($amount)
    {
        return $this->deposit >= $amount;
    }

    /**
     * Deposit amount
     */
    public function depositAmount($amount)
    {
        if(!User::amountIsValid($amount)){
            return false;
        }

        $this->deposit += $amount;
        $this->save();

        return true;
    }

    /**
     * Remove amount
     */
    public function removeAmount($amount)
    {
        if(!$this->hasAmount($amount)){
            return false;
        }
        
        $this->deposit -= $amount;
        $this->save();

        return true;
    }

    /**
     * Calculate the change for the user
     */
    public function calculateChange()
    {
        $change = [
            100 => 0,
            50 => 0,
            20 => 0,
            10 => 0,
            5 => 0
        ];

        if($this->deposit == 0){
            return $change;
        }

        foreach ($change as $key => $value) {
            $change[$key] = intdiv($this->deposit, $key);
            $this->deposit -= $key * $change[$key];
        }

        return $change;
    }
}
