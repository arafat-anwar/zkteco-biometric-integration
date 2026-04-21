<?php

namespace Modules\Receiver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PushLog extends Model
{
    use HasFactory;

    protected $table = 'push_logs';

    protected $fillable = [
        'organization_id',
        'device_id',
        'records_pushed',
        'records_saved',
        'duplicates_skipped',
        'status',
        'message',
        'pusher_ip',
    ];

    public function organization()
    {
        return $this->belongsTo(\Modules\Authentication\Models\Organization::class);
    }

    public function device()
    {
        return $this->belongsTo(\Modules\Credentials\Models\Device::class);
    }
}
