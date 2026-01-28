<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /**
     * Use UUID as primary key.
     */
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot function to auto-generate UUID for id.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'password',
        'first_name',
        'last_name',
        'agreed_to_terms',
        'dark_mode',
        'agreed_at',
    ];
    protected $appends = [
        'firstname',
        'lastname',
    ];

    public function getFirstnameAttribute()
    {
        return $this->attributes['first_name'] ?? '';
    }

    public function getLastnameAttribute()
    {
        return $this->attributes['last_name'] ?? '';
    }
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
        'password' => 'hashed',
        'agreed_to_terms' => 'boolean',
        'agreed_at' => 'datetime',
        'dark_mode' => 'boolean',
    ];
}
