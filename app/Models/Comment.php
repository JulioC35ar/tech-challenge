<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['maintenance_order_id', 'user_id', 'body'];

    public function maintenanceOrder()
    {
        return $this->belongsTo(MaintenanceOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
