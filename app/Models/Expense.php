<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

class Expense extends Model
{
    use HasUuids;

    protected $table = 'expenses';

    protected $fillable = [
        'name',
        'amount',
        'client_id',
        'category',
        'transaction_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
        'category' => 'array',
        'transaction_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
