<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = ['stake_amount'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function selections()
    {
        return $this->hasMany(BetSelection::class);
    }
}
