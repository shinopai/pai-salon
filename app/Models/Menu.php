<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'duration'
    ];

    /**
     * スタッフ情報とのリレーション（多対多）
     */
    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'staff_menus');
    }

    /**
     * 予約情報とのリレーション（1対多）
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
