<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const ADMIN = 1;
    const BUYER = 2;
    const SELLER = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name'
    ];

    public static function getAvailableRoles(){
        // return [self::ADMIN, self::BUYER, self::SELLER];
        return [self::BUYER, self::SELLER];
    }

}
