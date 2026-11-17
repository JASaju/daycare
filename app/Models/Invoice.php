<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        "employee_id", "child_id", "month", "year",
        "amount", "due_date", "status", "notes",
    ];

    // NOTE: "month" is intentionally NOT cast (it may hold a name like "January").
    protected $casts = [
        "due_date" => "date",
        "amount"   => "decimal:2",
        "year"     => "integer",
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /** Only APPROVED payments count (pending/rejected ones must not reduce the balance). */
    public function getPaidAmountAttribute()
    {
        return $this->relationLoaded('payments')
            ? $this->payments->where('status', 'approved')->sum('amount')
            : $this->payments()->where('status', 'approved')->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return round($this->amount - $this->paid_amount, 2);
    }
}
