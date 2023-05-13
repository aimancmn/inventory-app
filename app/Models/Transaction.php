<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_no', 'transaction_type', 'transaction_amount', 'transaction_status'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
