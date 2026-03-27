<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'tokens.*',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'Guru';
    }

    public function initials(): string
    {
        return (string) Str::of($this->name ?? '')
            ->trim()
            ->explode(' ')
            ->filter()
            ->map(fn(string $part) => Str::substr($part, 0, 1))
            ->take(2)
            ->implode('');
    }
}
