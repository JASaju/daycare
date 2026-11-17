<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $table = "children";

    protected $fillable = [
        "employee_id", "name", "date_of_birth", "gender",
        "photo", "notes", "status",
    ];

    protected $casts = [
        "date_of_birth" => "date",
    ];

    /** Only files we created ourselves look like this. */
    public const PHOTO_PATH_PATTERN = '#^uploads/children/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|png|webp)$#';

    public static function isManagedPhotoPath(?string $path): bool
    {
        return $path !== null && preg_match(self::PHOTO_PATH_PATTERN, $path) === 1;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function healthRecord()
    {
        return $this->hasOne(HealthRecord::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Public URL of the stored photo (public/uploads/children/...).
     * Null for empty values and for old Cloudinary / legacy "children/..." paths.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return self::isManagedPhotoPath($this->photo)
            ? asset($this->photo)
            : null;
    }

    /** Kept for backward compatibility with any view still using it; photos are already downscaled. */
    public function getPhotoThumbAttribute(): ?string
    {
        return $this->photo_url;
    }
}
