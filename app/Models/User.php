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
        'name',
        'email',
        'password',
        'tg_id',
        'age',
        'gender_id',
        'tg_username',
        'bio',
        'user_type',
        'status',
        'has_telegram_premium',
        'is_pro_user',
        'interested_in',
        'photos'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_pro_user' => 'boolean',
        'has_telegram_premium' => 'boolean'
    ];

    public function gender()
    {
        return $this->belongsTo(Gender::class)->select('id', 'name');
    }

    public function receivedLikes()
    {
        return $this->hasMany(Like::class, 'like_for');
    }

    public function receivedDislikes()
    {
        return $this->hasMany(Like::class, 'dislike_for');
    }

    public function sentLikes()
    {
        return $this->hasMany(Like::class, 'like_by');
    }

    public function sentDislikes()
    {
        return $this->hasMany(Like::class, 'dislike_by');
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests');
    }

    public function matches()
    {
        return $this->belongsToMany(User::class, 'user_matches', 'first_user_id', 'second_user_id')
            ->orWhere('user_matches.second_user_id', $this->id)
            ->where('user_matches.status', 1)
            ->withPivot('id');
    }


}
