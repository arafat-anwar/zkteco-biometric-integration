<?php

namespace Modules\Credentials\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Authentication\Models\Organization;

class Branch extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function attendanceEntries()
    {
        return $this->hasMany(\Modules\Receiver\Models\AttendanceEntry::class);
    }
}
