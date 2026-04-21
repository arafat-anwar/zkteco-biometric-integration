<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function devices()
    {
        return $this->hasMany(\Modules\Credentials\Models\Device::class);
    }

    public function attendanceEntries()
    {
        return $this->hasMany(\Modules\Receiver\Models\AttendanceEntry::class);
    }
}
