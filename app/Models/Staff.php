<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\StaffRole;

class Staff extends Model
{
    protected $table = 'staffs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
        ];
    }

    /**
     * ユーザー情報とのリレーション（1対多）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 予約情報とのリレーション（1対多）
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'staff_menus');
    }
}
