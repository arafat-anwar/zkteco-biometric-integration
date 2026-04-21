<?php

namespace Modules\Receiver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceEntry extends Model
{
    use HasFactory;

    protected $table = 'attendance_entries';

    protected $fillable = [
        'organization_id',
        'device_id',
        'branch_id',
        'emp_id',
        'real_emp_id',
        'check_time',
        'branch',
        'device_name',
    ];

    protected $casts = [
        'check_time' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(\Modules\Authentication\Models\Organization::class);
    }

    public function device()
    {
        return $this->belongsTo(\Modules\Credentials\Models\Device::class);
    }

    public function branch()
    {
        return $this->belongsTo(\Modules\Credentials\Models\Branch::class);
    }
}
