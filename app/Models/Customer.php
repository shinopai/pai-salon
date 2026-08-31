<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email'
    ];

    /**
     * 予約情報とのリレーション（1対多）
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
