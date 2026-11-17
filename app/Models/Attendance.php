<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        "child_id", "date", "check_in", "check_out",
        "checked_in_by", "checked_out_by", "notes",
    ];

    protected $casts = [
        "date" => "date",
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(Staff::class, "checked_in_by");
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(Staff::class, "checked_out_by");
    }
}
