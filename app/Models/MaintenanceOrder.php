<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'asset_id',
        'status',
        'priority',
        'technician_id',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => 'string',
        'priority' => 'string',
    ];

    const STATUS_CREATED = 'created';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}


