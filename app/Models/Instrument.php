<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "category", "quantity", "condition",
        "purchase_date", "purchase_price", "notes",
    ];

    protected $casts = [
        "purchase_date"  => "date",
        "purchase_price" => "decimal:2",
        "quantity"       => "integer",
    ];
}
