<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed balance
 */
class Player extends Model
{
    use HasFactory;

    const DEFAULT_BALANCE = 1_000;

    protected $fillable = ['balance', 'created_at', 'updated_at'];


    /**
     * Checking ability to Betting.
     *
     * @param $amount
     * @return bool
     */
    public function isBalanceSufficient($amount)
    {
        return $this->balance >= $amount;
    }

    public function transactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    /**
     * Expend Amount
     *
     * @param $amount
     * @return bool
     */
    public function updateBalance($amount)
    {
        $this->balance -= $amount;
        return $this->save();
    }
}
