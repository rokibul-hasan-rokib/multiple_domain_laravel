<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stripe\Climate\Order;

class OrderDetail extends Model
{
    protected $guarded = [];
    protected $casts = [
        'validity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function getValidityAttribute($value)
    {
        return $value / 86400; // Convert seconds to days
    }
    public function setValidityAttribute($value)
    {
        $this->attributes['validity'] = $value * 86400; // Convert days to seconds
    }
    public function getValidityInDaysAttribute()
    {
        return $this->validity / 86400; // Convert seconds to days
    }
    public function getValidityInHoursAttribute()
    {
        return $this->validity / 3600; // Convert seconds to hours
    }
    public function getValidityInMinutesAttribute()
    {
        return $this->validity / 60; // Convert seconds to minutes
    }
    public function getValidityInSecondsAttribute()
    {
        return $this->validity; // Return seconds
    }

    final public function prepareData(Package $package, Order $order): array
    {
        return [
            'order_id' => $order->id,
            'name' => $package->name,
            'price' => $package->price,
            'currency' => Package::CURRENCY,
            'validity' => $package->validity,
            'feature' => $package->feature,
        ];
    }
}

