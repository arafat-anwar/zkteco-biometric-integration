<?php

namespace Modules\Credentials\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'ip_address',
        'port',
        'serial_number',
        'model',
        'location',
        'mdb_path',
        'connection_type',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(\Modules\Authentication\Models\Organization::class);
    }

    public function attendanceEntries()
    {
        return $this->hasMany(\Modules\Receiver\Models\AttendanceEntry::class);
    }

    public function pushLogs()
    {
        return $this->hasMany(\Modules\Receiver\Models\PushLog::class);
    }
}
