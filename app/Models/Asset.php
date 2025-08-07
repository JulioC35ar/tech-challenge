<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
      'name',
    ];
    
    public function maintenanceOrders()
    {
      return $this->hasMany(MaintenanceOrder::class);
    }
}

