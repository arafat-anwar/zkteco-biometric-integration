<?php

namespace Modules\Credentials\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Authentication\Models\Organization;

class Employee extends Model
{
    protected $fillable = [
        'organization_id',
        'mdb_user_id',
        'badge_number',
        'name',
        'card_no',
        'department_id',
        'privilege',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
