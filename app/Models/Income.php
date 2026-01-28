<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Income extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'name', 'amount', 'category', 'transaction_date', 'note', 'client_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'double',
        'category' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\User::class, 'client_id');
    }
}
