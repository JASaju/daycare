<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        "invoice_id", "amount", "paid_date", "method", "reference", "status",
    ];

    protected $casts = [
        "paid_date" => "date",
        "amount"    => "decimal:2",
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
