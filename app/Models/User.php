<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'default_filter',
        'default_sort',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Find a user by email, username or phone.
     */
    public function scopeByIdentifier(Builder $query, string $identifier): Builder
    {
        $identifier = trim($identifier);
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $identifier);

        return $query->where(function (Builder $q) use ($identifier, $normalizedPhone) {
            $q->where('email', $identifier)
                ->orWhere('email', mb_strtolower($identifier))
                ->orWhere('username', $identifier)
                ->orWhere('phone', $identifier)
                ->orWhere('phone', $normalizedPhone);
        });
    }

    /**
     * Tasks that belong to this user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
